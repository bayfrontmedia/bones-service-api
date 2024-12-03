<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\HttpRequest\Request;

abstract class AuthApiController extends ApiController
{

    /**
     * @param ApiService $apiService
     * @throws ApiHttpException
     * @throws ApiServiceException
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