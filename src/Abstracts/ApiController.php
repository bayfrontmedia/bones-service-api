<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Controller;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\MethodNotAllowedException;
use Bayfront\BonesService\Api\Exceptions\Http\NotAcceptableException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Api\Exceptions\Http\PaymentRequiredException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Bayfront\LeakyBucket\AdapterException;
use Bayfront\LeakyBucket\Bucket;
use Bayfront\LeakyBucket\BucketException;
use Exception;
use Throwable;

abstract class ApiController extends Controller
{

    public ApiService $apiService;
    public EventService $events;
    public FilterService $filters;
    public RbacService $rbacService;
    public Response $response;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        $this->events = $this->apiService->events;
        $this->filters = $this->apiService->filters;
        $this->rbacService = $this->apiService->rbacService;
        $this->response = $this->apiService->response;

        parent::__construct($this->events); // Fires the bones.controller event
        $this->events->doEvent('api.controller', $this);
    }

    /*
     * Override global API configuration
     */
    public bool $check_required_headers = true;
    public bool $check_https = true;
    public bool $check_ip_whitelist = true;
    public bool $set_required_headers = true;

    /**
     * Enforce rate limit and set X-RateLimit headers.
     * On error, aborts with 429 HTTP status.
     *
     * @param string $id
     * @param int $limit
     * @return void
     * @throws ApiExceptionInterface
     */
    protected function enforceRateLimit(string $id, int $limit): void
    {

        try {

            /** @var Bucket $bucket */
            $bucket = App::make('Bayfront\LeakyBucket\Bucket', [
                'id' => $id,
                'settings' => [
                    'capacity' => $limit,
                    'leak' => $limit // TODO: $limit for burstable, 1 for hard limit - test which is best
                ]
            ]);

        } catch (Exception $e) {
            $this->abort(500, $e->getMessage());
        }

        try {
            $bucket->leak()->fill()->save();
        } catch (BucketException) {

            $wait = round($bucket->getSecondsUntilCapacity());

            $this->response->setHeaders([
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => floor($bucket->getCapacityRemaining()),
                'X-RateLimit-Reset' => round($bucket->getSecondsUntilEmpty()),
                'Retry-After' => $wait
            ]);

            $this->abort(429, 'Rate limit exceeded. Try again in ' . $wait . ' seconds');

        } catch (AdapterException $e) {
            $this->abort(500, $e->getMessage());
        }

        $this->response->setHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => floor($bucket->getCapacityRemaining()),
            'X-RateLimit-Reset' => round($bucket->getSecondsUntilEmpty())
        ]);

    }

    /**
     * Require headers.
     * On error, aborts with 400 HTTP status.
     *
     * @param array $headers
     * @return void
     * @throws ApiExceptionInterface
     */
    protected function requireHeaders(array $headers): void
    {

        foreach ($headers as $k => $v) {

            if (Request::getHeader($k) !== $v) {
                $this->abort(400, 'Required header missing or invalid: ' . $k);
            }

        }

    }

    /**
     * Get JSON-encoded request body.
     *
     * @param array $allowed (Allowed fields)
     * @param array $required (Required fields)
     * @return array
     * @throws ApiExceptionInterface
     */
    protected function getBody(array $allowed = [], array $required = []): array
    {

        $body = json_decode(Request::getBody(), true);

        if (!$body || !is_array($body)) {

            if (empty($required)) {
                return [];
            } else {
                $this->abort(400, 'Unable to get body: Invalid or missing JSON body');
            }

        }

        if (!empty($allowed) && !empty(Arr::except($body, $allowed))) {
            $this->abort(400, 'Unable to get body: Invalid fields');
        }

        if (!empty($required) && Arr::isMissing($body, $required)) {
            $this->abort(400, 'Unable to get body: Missing required fields');
        }

        return $body;

    }

    /**
     * Send API response.
     *
     * - Filters response using the api.response filter
     * - Triggers the api.response event
     * - Sends $data as json_encoded string
     *
     * @param int $status_code (HTTP status code to send)
     * @param array $data (Data to send)
     * @param array $headers (Key/value pairs of header values to send)
     * @return void
     * @throws ApiExceptionInterface
     */
    protected function respond(int $status_code = 200, array $data = [], array $headers = []): void
    {

        $data = (array)$this->filters->doFilter('api.response', $data);

        try {

            $this->response->setStatusCode($status_code)->setHeaders($headers);

            if (!empty($data)) { // Only setBody if one exists
                $this->response->setBody(json_encode($data));
            }

        } catch (InvalidStatusCodeException) {
            $this->abort(500, 'Unable to respond: Invalid status code (' . $status_code . ')');
        }

        $this->events->doEvent('api.response', $this);

        $this->response->send();

    }

    /**
     * Aborts with appropriate API exception based on status code.
     *
     * @param int $status_code
     * @param string $message
     * @param Throwable|null $previous
     * @return void
     * @throws ApiExceptionInterface
     */
    protected function abort(int $status_code, string $message = '', Throwable $previous = null): void
    {

        $exceptions = [
            400 => BadRequestException::class,
            401 => UnauthorizedException::class,
            402 => PaymentRequiredException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            405 => MethodNotAllowedException::class,
            406 => NotAcceptableException::class,
            409 => ConflictException::class,
            429 => TooManyRequestsException::class
        ];

        if (isset($exceptions[$status_code])) {
            throw new $exceptions[$status_code]($message, 0, $previous);
        }

        throw new ApiServiceException($message, 0, $previous);

    }

}