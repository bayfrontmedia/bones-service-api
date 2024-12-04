<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArraySchema\SchemaInterface;

class ServerStatusResource implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        return [
            'data' => $array
        ];
    }

}