<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 409.
 */
class ConflictException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 409;
    }

}