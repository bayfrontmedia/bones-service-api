<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 405.
 */
class MethodNotAllowedException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 405;
    }

}