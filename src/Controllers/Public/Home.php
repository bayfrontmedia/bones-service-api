<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\BonesService\Api\Abstracts\PublicApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\HttpRequest\Request;

class Home extends PublicApiController
{

    public function __construct(ApiService $apiService)
    {
        $this->check_required_headers = false;
        parent::__construct($apiService);
    }

    public function index(): void
    {
        $this->respond(200, [
            'status' => 'OK',
            'clientIp' => Request::getIp(),
            'apiVersion' => $this->apiService->getConfig('version'),
            'date' => date('Y-m-d H:i:s')
        ]);
    }

}