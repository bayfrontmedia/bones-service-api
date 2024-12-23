<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class TenantObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'id',
            'owner',
            'domain',
            'name',
            'meta',
            'enabled',
            'created_at',
            'updated_at'
        ];

        return Arr::order(Arr::only($array, $keys), $keys);
    }

}