<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 404.
 */
class NotFoundException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 404;
    }

}