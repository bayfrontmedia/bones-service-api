<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;

class Permissions extends PrivateApiController
{

    /**
     * List permissions.
     *
     * @return void
     * @throws ApiExceptionInterface
     */
    public function list(): void
    {
        $this->respond(200, $this->user->read());
    }

}