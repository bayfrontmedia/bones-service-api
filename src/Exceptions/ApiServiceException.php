<?php

namespace Bayfront\BonesService\Api\Exceptions;

use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;

/**
 * Unexpected exception.
 * HTTP status 500.
 */
class ApiServiceException extends ServiceException implements ApiExceptionInterface
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 500;
    }

}