<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\ArraySchema\SchemaInterface;

class ErrorResource implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        $keys = [
            'status',
            'title',
            'message',
            'link',
            'code',
            'request_id',
            'elapsed',
            'time'
        ];

        return [
            'error' => Arr::order(Arr::only($array, $keys), $keys)
        ];

    }

}