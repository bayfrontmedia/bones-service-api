<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArraySchema\SchemaInterface;

class ErrorResource implements SchemaInterface
{

    /**
     * @inheritDoc
     *
     * TODO:
     * - status (HTTP status)
     * - title (HTTP status title)
     * - detail (Exception message)
     * - code (Exception code)
     * - request ID
     *
     * Also link to documentation?
     */
    public static function create(array $array, array $config = []): array
    {
        return [];
    }
}