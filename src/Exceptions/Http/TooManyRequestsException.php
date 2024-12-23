<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

use Bayfront\BonesService\Api\Exceptions\ApiHttpException;

/**
 * HTTP status 429.
 */
class TooManyRequestsException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 429;
    }

}