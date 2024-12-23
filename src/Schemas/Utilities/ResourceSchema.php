<?php

namespace Bayfront\BonesService\Api\Schemas\Utilities;

use Bayfront\ArraySchema\SchemaInterface;

class ResourceSchema implements SchemaInterface
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