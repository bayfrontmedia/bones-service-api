<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

use Bayfront\Bones\Exceptions\HttpException;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;

/**
 * HTTP status 429.
 */
class TooManyRequestsException extends HttpException implements ApiExceptionInterface
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 429;
    }

}