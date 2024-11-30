<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\BonesService\Api\Abstracts\PrivateApiController;

class Permissions extends PrivateApiController
{

    public function list(): void
    {
        $this->apiService->respond(200, $this->user->read());
    }

}