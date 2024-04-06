<?php

namespace Bayfront\BonesService\Api\Events;

use Bayfront\Bones\Abstracts\EventSubscriber;
use Bayfront\Bones\Application\Services\Events\EventSubscription;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Interfaces\EventSubscriberInterface;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\NotAcceptableException;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Throwable;

class ApiServiceEvents extends EventSubscriber implements EventSubscriberInterface
{

    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptions(): array
    {
        return [
            new EventSubscription('api.controller', [$this, 'checkRequiredHeaders'], 5),
            new EventSubscription('api.controller', [$this, 'checkHttps'], 5),
            new EventSubscription('api.controller', [$this, 'checkIpWhitelist'], 5),
            new EventSubscription('api.response', [$this, 'setRequiredHeaders'], 5),
            new EventSubscription('bones.exception', [$this, 'setStatusCode'], 5)
        ];
    }

    /**
     * Check for required request headers.
     *
     * @return void
     * @throws ApiExceptionInterface
     */
    public function checkRequiredHeaders(): void
    {

        $required_headers = $this->apiService->getConfig('request.headers', []);

        foreach ($required_headers as $k => $v) {

            if (Request::getHeader($k) !== $v) {
                throw new BadRequestException('Required header missing or invalid: ' . $k);
            }

        }

    }

    /**
     * Check request is made over HTTPS.
     *
     * @return void
     * @throws ApiExceptionInterface
     */
    public function checkHttps(): void
    {

        if (!Request::isHttps() && in_array(App::environment(), $this->apiService->getConfig('request.https_env', []))) {
            throw new NotAcceptableException('All requests must be made over HTTPS');
        }

    }

    /**
     * Restrict access by IP.
     *
     * @return void
     * @throws ApiExceptionInterface
     */
    public function checkIpWhitelist(): void
    {

        $whitelist = $this->apiService->getConfig('request.ip_whitelist', []);

        if (!empty($whitelist)) {

            $ip = Request::getIp();

            if (!in_array($ip, $whitelist)) {
                throw new ForbiddenException('IP not allowed: ' . $ip);
            }

        }

    }

    /**
     * Set required response headers.
     *
     * @param Response $response
     * @return void
     */

    public function setRequiredHeaders(Response $response): void
    {
        $response->setHeaders($this->apiService->getConfig('response.headers', []));
    }

    /**
     * Set HTTP status code for thrown exception.
     *
     * @param Response $response
     * @param Throwable $e
     * @return void
     * @throws InvalidStatusCodeException
     */
    public function setStatusCode(Response $response, Throwable $e): void
    {

        if ($e instanceof ApiExceptionInterface) {
            $response->setStatusCode($e->getHttpStatusCode());
        }

    }

}