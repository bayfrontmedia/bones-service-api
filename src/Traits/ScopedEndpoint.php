<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;

trait ScopedEndpoint
{

    /**
     * Ensure scoped fields do not exist in array, then set their value.
     * On error, aborts with 400 HTTP status.
     *
     * Helpful when creating or updating a scoped resource where values
     * are set by path parameters instead of the body.
     *
     * @param array $array
     * @param array $values
     * @return array
     * @throws ApiHttpException
     */
    protected function validateScopedFields(array $array, array $values): array
    {
        foreach (array_keys($values) as $field) {
            if (isset($array[$field])) {
                throw new BadRequestException('Bad request: Invalid field (' . $field . ')');
            }
        }

        return array_merge($array, $values);
    }

}