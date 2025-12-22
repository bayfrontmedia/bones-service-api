<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

use Bayfront\BonesService\Api\Exceptions\ApiHttpException;

/**
 * HTTP status 424.
 */
class FailedDependencyException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 424;
    }

}