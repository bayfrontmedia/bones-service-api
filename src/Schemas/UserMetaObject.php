<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class UserMetaObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'id',
            'user',
            'meta_key',
            'meta_value',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        return Arr::order(Arr::only($array, $keys), $keys);
    }

}