<?php

namespace Bayfront\BonesService\Api\Schemas\Utilities;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;
use Bayfront\BonesService\Api\Schemas\AggregateObject;
use Bayfront\BonesService\Api\Schemas\CursorPaginationObject;
use Bayfront\BonesService\Api\Schemas\PagePaginationObject;

class CollectionSchema implements SchemaInterface
{

    /**
     * Create collection schema.
     *
     * Adds aggregate and pagination keys based on config values.
     *
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {

        $array = [
            'data' => $array
        ];

        if (isset($config['aggregate'])) {
            $array['aggregate'] = AggregateObject::create($config['aggregate']);
        }

        if (isset($config['pagination_type']) && isset($config['pagination'])) {

            if ($config['pagination_type'] == 'page') {
                $array['pagination'] = PagePaginationObject::create($config['pagination']);
            } else if ($config['pagination_type'] == 'cursor') {
                $array['pagination'] = CursorPaginationObject::create($config['pagination']);
            }

        }

        $keys = [
            'data',
            'aggregate',
            'pagination'
        ];

        return Arr::order(Arr::only($array, $keys), $keys);

    }

}