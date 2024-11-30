<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\Bones\Abstracts\Controller;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiControllerInterface;

abstract class ApiController extends Controller implements ApiControllerInterface
{

    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        parent::__construct($this->apiService->events); // Fires the bones.controller event
        $this->apiService->events->doEvent('api.controller', $this);
    }

}