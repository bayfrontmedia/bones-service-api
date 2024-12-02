<?php

namespace Bayfront\BonesService\Api\Exceptions\Http;

/**
 * HTTP status 402.
 */
class PaymentRequiredException extends ApiHttpException
{

    /**
     * @inheritDoc
     */
    public function getHttpStatusCode(): int
    {
        return 402;
    }

}