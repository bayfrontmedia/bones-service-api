<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

use Bayfront\BonesService\Api\Exceptions\ApiHttpException;

/**
 * HTTP status 426.
 */
class UpgradeRequiredException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 426;
    }

}