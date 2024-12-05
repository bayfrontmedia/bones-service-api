<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PublicApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Schemas\ServerStatusResource;

class Home extends PublicApiController
{

    /**
     * @param ApiService $apiService
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function __construct(ApiService $apiService)
    {
        $this->check_required_headers = false;
        parent::__construct($apiService);
    }

    /**
     * @return void
     * @throws ApiServiceException
     * @throws ApiHttpException
     */
    public function index(): void
    {

        $this->respond(200, ServerStatusResource::create([
            'status' => 'ok'
        ]), [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate'
        ]);

    }

}