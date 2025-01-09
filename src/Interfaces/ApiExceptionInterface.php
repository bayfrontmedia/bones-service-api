<?php

namespace Bayfront\BonesService\Api\Interfaces;

interface ApiExceptionInterface
{

    /**
     * HTTP status code to return for this exception.
     *
     * @return int
     */
    public function getHttpStatusCode(): int;

}