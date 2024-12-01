<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\Bones\Application\Utilities\App;
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

        // Schema
        $schema = [
            'data' => [
                'status' => 'OK',
                'version' => $this->apiService->getConfig('version', ''),
                'clientIp' => Request::getIp(),
                'date' => date('c'),
                'elapsed' => App::getElapsedTime()
            ]
        ];

        $this->respond(200, $schema, [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate'
        ]);
    }

}