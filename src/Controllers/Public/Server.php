<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PublicApiController;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Api\Schemas\ServerStatusResource;

class Server extends PublicApiController
{

    /**
     * @param ApiService $apiService
     * @throws ApiServiceException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService)
    {
        $this->check_required_headers = false;
        parent::__construct($apiService);
    }

    /**
     * Get server status.
     *
     * @return void
     * @throws ApiServiceException
     */
    public function status(): void
    {

        $this->respond(200, ServerStatusResource::create([
            'status' => 'OK'
        ]), [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate'
        ]);

    }

}