<?php

namespace Bayfront\BonesService\Api\Controllers\Public;

use Bayfront\BonesService\Api\Abstracts\PublicApiController;

class Home extends PublicApiController
{

    public function index(): void
    {
        $this->apiService->respond(200, [
            'status' => 'ok'
        ]);
    }

}