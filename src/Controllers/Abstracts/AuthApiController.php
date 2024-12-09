<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\HttpRequest\Request;

abstract class AuthApiController extends ApiController
{

    /**
     * @param ApiService $apiService
     * @throws ApiServiceException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);

        // Rate limit

        if ((int)$this->apiService->getConfig('rate_limit.auth', 0) > 0) {
            $this->enforceRateLimit(md5('auth-' . Request::getIp()), (int)$this->apiService->getConfig('rate_limit.auth'));
        }

        $this->events->doEvent('api.controller.auth', $this);
    }

}