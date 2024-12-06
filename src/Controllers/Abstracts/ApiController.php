<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Controller;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Helpers;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
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
use Bayfront\BonesService\Api\Traits\Auditable;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Bayfront\LeakyBucket\AdapterException;
use Bayfront\LeakyBucket\Bucket;
use Bayfront\LeakyBucket\BucketException;
use Bayfront\Validator\Validator;
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
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function enforceRateLimit(string $id, int $limit): void
    {

        try {

            /** @var Bucket $bucket */
            $bucket = App::make('Bayfront\LeakyBucket\Bucket', [
                'id' => $id,
                'settings' => [
                    'capacity' => $limit, // Total bucket capacity
                    'leak' => $limit // Number of drops to leak per minute
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

            if ($this instanceof AuthApiController) {
                $this->events->doEvent('api.auth.limit');
            }

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
     * @throws ApiHttpException
     * @throws ApiServiceException
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
     * Require permissions for non-admin user.
     * On error, aborts with 403 HTTP status.
     *
     * @param User $user
     * @param string $tenant_id
     * @param array $permission_names
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function requirePermissions(User $user, string $tenant_id, array $permission_names): void
    {

        if ($user->isAdmin()) {
            return;
        }

        try {

            if (!$user->canDoAll($tenant_id, $permission_names)) {
                $this->abort(403, 'Forbidden: Insufficient permissions');
            }

        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to verify permissions: Unexpected error', 0, $e);
        }

    }

    /**
     * Get JSON-encoded request body.
     * On error, aborts with 400 HTTP status.
     *
     * See: https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md
     *
     * TODO:
     * Need to validate only allowed keys exist in body.
     * i.e.: Auth->login OTP
     *
     * @param array $validation_rules (Validation rules for fields)
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function getBody(array $validation_rules = []): array
    {

        $body = json_decode(Request::getBody(), true);

        if (!$body || !is_array($body)) {
            $this->abort(400, 'Unable to get body: Invalid or missing JSON');
        }

        if (!empty($validation_rules)) {

            $validator = new Validator();
            $validator->validate($body, $validation_rules, false, true);

            if (!$validator->isValid()) {
                $this->abort(400, 'Unable to get body: Invalid or missing field(s)');
            }

        }

        return $body;

    }

    /**
     * Get URL query parameters.
     * On error, aborts with 400 HTTP status.
     *
     * TODO:
     * Revisit allowed/required
     *
     * @param array $allowed (Allowed keys)
     * @param array $required (Required keys)
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function getQuery(array $allowed = [], array $required = []): array
    {

        $query = Request::getQuery();

        if (!empty($allowed) && !empty(Arr::except($query, $allowed))) {
            $this->abort(400, 'Unable to get query: Invalid key(s)');
        }

        if (!empty($required) && Arr::isMissing($query, $required)) {
            $this->abort(400, 'Unable to get query: Missing required key(s)');
        }

        return $query;

    }

    /**
     * Send API response.
     *
     * - Filters response using the api.response filter
     * - Triggers the api.auditable event if needed
     * - Triggers the api.response event
     * - Sends $data as json_encoded string
     *
     * @param int $status_code (HTTP status code to send)
     * @param array $data (Data to send)
     * @param array $headers (Key/value pairs of header values to send)
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
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

        if (in_array(Auditable::class, Helpers::classUses($this))) {

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

            /** @var Auditable $this */

            if (in_array(Arr::get($backtrace, '1.function'), $this->getAuditableFunctions())) {
                $this->events->doEvent('api.auditable', $this, Arr::get($backtrace, '1.class', ''), Arr::get($backtrace, '1.function', ''));
            }

        }

        $this->events->doEvent('api.response', $this);

        $this->response->send();

    }

    /**
     * Aborts with appropriate API exception based on status code.
     *
     * @param int $status_code
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return no-return
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function abort(int $status_code, string $message = '', int $code = 0, Throwable $previous = null): void
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
            throw new $exceptions[$status_code]($message, $code, $previous);
        }

        throw new ApiServiceException($message, $code, $previous);

    }

}