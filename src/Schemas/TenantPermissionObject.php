<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class TenantPermissionObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'id',
            'tenant',
            'permission',
            'created_at',
            'updated_at'
        ];

        return Arr::order(Arr::only($array, $keys), $keys);
    }

}