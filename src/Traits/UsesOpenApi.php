<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\OpenApi\Objects\OperationObject;

trait UsesOpenApi
{

    protected function validateOasOperation(OperationObject $operationObject, array $params): void
    {

        // Check permissions

        // Check parameters - path, query, header, cookie

        // Validate body

    }

}