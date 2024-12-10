<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class TenantTeamsObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'id',
            'tenant',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at'
        ];

        return Arr::order(Arr::only($array, $keys), $keys);
    }

}