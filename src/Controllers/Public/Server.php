<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PublicApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Schemas\ServerStatusResource;

class Server extends PublicApiController
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
     * Get server status.
     *
     * @return void
     * @throws ApiServiceException
     * @throws ApiHttpException
     */
    public function status(): void
    {

        $this->respond(200, ServerStatusResource::create([
            'status' => 'ok'
        ]), [
            'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate'
        ]);

    }

    /**
     * Get OpenAPI specification (OAS).
     *
     * TODO:
     * If the API service requires OpenAPI, a config setting would not be needed
     * as an OpenApiObject can be injected into the API service constructor.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function oas(): void
    {
        $oas = json_decode(file_get_contents($this->apiService->getConfig('oas')), true);
        $this->respond(200, $oas);
    }

}