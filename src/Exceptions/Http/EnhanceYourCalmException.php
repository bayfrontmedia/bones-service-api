<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

use Bayfront\BonesService\Api\Exceptions\ApiHttpException;

/**
 * HTTP status 420.
 */
class EnhanceYourCalmException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 420;
    }

}