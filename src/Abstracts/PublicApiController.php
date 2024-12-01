<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\HttpRequest\Request;

class PublicApiController extends ApiController
{

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