<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\BonesService\Api\Abstracts\PublicApiController;
use Bayfront\BonesService\Api\ApiService;

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
                'status' => 'OK'
            ]
        ];

        $this->respond(200, $schema, [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate'
        ]);
    }

}