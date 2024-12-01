<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\HttpRequest\Request;

class PublicApiController extends ApiController
{

    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);

        // Rate limit

        if ((int)$this->apiService->getConfig('rate_limit.public', 0) > 0) {
            $this->rateLimitOrAbort('public-' . Request::getIp(), (int)$this->apiService->getConfig('rate_limit.public'));
        }

        $this->apiService->events->doEvent('api.controller.public', $this);
    }

}