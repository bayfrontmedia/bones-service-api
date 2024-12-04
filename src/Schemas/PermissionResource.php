<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArraySchema\SchemaInterface;
use Bayfront\BonesService\Api\Schemas\Types\ResourceSchema;

class PermissionResource implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        return ResourceSchema::create($array);
    }

}