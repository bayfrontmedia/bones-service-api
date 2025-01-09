<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class PagePaginationObject implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'results.current',
            'results.total',
            'results.from',
            'results.to',
            'page.size',
            'page.current',
            'page.previous',
            'page.next',
            'page.total'
        ];

        return Arr::undot(Arr::only(Arr::dot($array), $keys));
    }

}