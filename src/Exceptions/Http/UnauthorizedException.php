<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 401.
 */
class UnauthorizedException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 401;
    }

}