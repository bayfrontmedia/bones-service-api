<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\HttpRequest\Request;

abstract class PublicApiController extends ApiController
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

        if ((int)$this->apiService->getConfig('rate_limit.public', 0) > 0) {
            $this->enforceRateLimit(md5('public-' . Request::getIp()), (int)$this->apiService->getConfig('rate_limit.public'));
        }

        $this->events->doEvent('api.controller.public', $this);
    }

}