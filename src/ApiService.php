<?php

namespace Bayfront\BonesService\Api;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Service;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Api\Events\ApiServiceEvents;
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
use Bayfront\BonesService\Api\Filters\ApiServiceFilters;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Throwable;

class ApiService extends Service
{

    public EventService $events;
    public FilterService $filters;
    public Response $response;
    protected array $config;

    /**
     * The container will resolve any dependencies.
     * EventService is required by the abstract service.
     *
     * @param EventService $events
     * @param FilterService $filters
     * @param Response $response
     * @param array $config
     * @throws ApiExceptionInterface
     */

    public function __construct(EventService $events, FilterService $filters, Response $response, array $config)
    {
        $this->events = $events;
        $this->filters = $filters;
        $this->response = $response;
        $this->config = $config;

        parent::__construct($events);

        // Enqueue events

        try {
            $this->events->addSubscriptions(new ApiServiceEvents($this));
        } catch (ServiceException $e) {
            throw new ApiServiceException('Unable to start ApiService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        // Enqueue filters

        try {
            $this->filters->addSubscriptions(new ApiServiceFilters($this));
        } catch (ServiceException $e) {
            throw new ApiServiceException('Unable to start ApiService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $this->events->doEvent('api.start', $this);

    }

    /**
     * Get API configuration value in dot notation.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $key = '', mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * Get JSON-encoded request body.
     *
     * @param bool $required (Throws BadRequestException if required and not existing)
     * @return array
     * @throws ApiExceptionInterface
     */
    public function getBody(bool $required = false): array
    {

        $body = json_decode(Request::getBody(), true);

        if (!$body || !is_array($body)) {

            if ($required === true) {
                throw new BadRequestException('Unable to get body: Invalid or missing JSON body');
            } else {
                return [];
            }

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
     * @throws ApiServiceException
     */
    public function respond(int $status_code = 200, array $data = [], array $headers = []): void
    {

        $data = (array)$this->filters->doFilter('api.response', $data);

        try {

            $this->response->setStatusCode($status_code)->setHeaders($headers);

            if (!empty($data)) { // Only setBody if one exists
                $this->response->setBody(json_encode($data));
            }

        } catch (InvalidStatusCodeException) {
            throw new ApiServiceException('Unable to respond: Invalid status code (' . $status_code . ')');
        }

        $this->events->doEvent('api.response', $this->response);

        $this->response->send();

    }

    /**
     * Throws appropriate API exception based on status code.
     *
     * @param int $status_code
     * @param string $message
     * @param Throwable|null $previous
     * @return no-return
     * @throws ApiExceptionInterface
     */
    public function throwException(int $status_code, string $message = '', Throwable $previous = null): void
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