<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;

class Permissions extends PrivateApiController
{

    /**
     * List permissions.
     *
     * @return void
     * @throws ApiServiceException
     */
    public function list(): void
    {
        $this->apiService->respond(200, $this->user->read());
    }

}