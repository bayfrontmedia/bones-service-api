<?php

namespace Bayfront\BonesService\Api\Schemas;

use Bayfront\ArraySchema\SchemaInterface;
use Bayfront\BonesService\Api\Schemas\Utilities\ResourceSchema;

class TenantTeamResource implements SchemaInterface
{

    /**
     * @inheritDoc
     */
    public static function create(array $array, array $config = []): array
    {
        return ResourceSchema::create(TenantTeamObject::create($array), $config);
    }

}