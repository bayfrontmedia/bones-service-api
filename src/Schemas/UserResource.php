<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArraySchema\SchemaInterface;
use Bayfront\BonesService\Api\Schemas\Utilities\ResourceSchema;

class UserResource implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        return ResourceSchema::create(UserObject::create($array), $config);
    }

}