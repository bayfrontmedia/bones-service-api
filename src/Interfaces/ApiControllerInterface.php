<?php

namespace Bayfront\BonesService\Api\Interfaces;

interface ApiControllerInterface
{

    /**
     * Is controller private?
     *
     * This value determines which event to trigger:
     *   - api.controller.public
     *   - api.controller.private
     *
     * @return bool
     */
    public function isPrivate(): bool;

}