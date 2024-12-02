<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 403.
 */
class ForbiddenException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 403;
    }

}