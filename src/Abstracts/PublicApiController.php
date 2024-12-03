<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\HttpRequest\Request;

abstract class PublicApiController extends ApiController
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

        if ((int)$this->apiService->getConfig('rate_limit.public', 0) > 0) {
            $this->enforceRateLimit(md5('public-' . Request::getIp()), (int)$this->apiService->getConfig('rate_limit.public'));
        }

        $this->events->doEvent('api.controller.public', $this);
    }

}