<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class CursorPaginationObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'results.current',
            'cursor.first',
            'cursor.last'
        ];

        return Arr::undot(Arr::only(Arr::dot($array), $keys));
    }

}