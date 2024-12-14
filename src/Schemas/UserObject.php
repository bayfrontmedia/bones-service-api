<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class UserObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'id',
            'email',
            'meta',
            'admin',
            'enabled',
            'created_at',
            'updated_at',
            'verified_at'
        ];

        return Arr::order(Arr::only($array, $keys), $keys);
    }

}