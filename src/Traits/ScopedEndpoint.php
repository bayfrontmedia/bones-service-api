<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\Http\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;

trait ScopedEndpoint
{

    /**
     * Ensure scoped fields do not exist in array, then set their value.
     * On error, aborts with 400 HTTP status.
     *
     * Helpful when creating a scoped resource.
     *
     * @param array $array
     * @param array $values
     * @return array
     * @throws ApiHttpException
     */
    protected function defineScopedFields(array $array, array $values): array
    {
        $array = $this->disallowScopedFields($array, array_keys($values));
        return array_merge($array, $values);
    }

    /**
     * Ensure scoped fields do not exist in array.
     * On error, aborts with 400 HTTP status.
     *
     * Helpful when updating a scoped resource.
     *
     * @param array $array
     * @param array $field_names
     * @return array
     * @throws ApiHttpException
     */
    protected function disallowScopedFields(array $array, array $field_names): array
    {

        foreach ($field_names as $field) {
            if (isset($array[$field])) {
                throw new BadRequestException('Bad request: Invalid field (' . $field . ')');
            }
        }

        return $array;

    }

}