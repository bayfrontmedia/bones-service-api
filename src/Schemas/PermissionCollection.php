<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class PermissionCollection implements SchemaInterface
{

    /**
     * Array keys:
     * - data
     * - pagination
     * - aggregate
     *
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {

        $return = [
            'data' => Arr::get($array, 'data', [])
        ];

        if (isset($array['pagination'])) {
            $return['pagination'] = PagePagination::create($array['pagination']);
        }

        return $return;

    }

}