<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArraySchema\SchemaInterface;
use Bayfront\BonesService\Api\Schemas\Utilities\CollectionSchema;

class TenantMetaCollection implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {

        foreach ($array as $k => $v) {
            $array[$k] = TenantMetaObject::create($v);
        }

        return CollectionSchema::create($array, $config);

    }

}