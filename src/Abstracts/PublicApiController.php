<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\BonesService\Api\ApiService;

class PublicApiController extends ApiController
{

    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);
        $this->apiService->events->doEvent('api.controller.public', $this);
    }

}