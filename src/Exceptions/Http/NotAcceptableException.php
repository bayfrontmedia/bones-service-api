<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 406.
 */
class NotAcceptableException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 406;
    }

}