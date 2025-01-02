<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Models\ResourceModel;
use Bayfront\BonesService\Orm\Utilities\Parsers\FieldParser;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\HttpRequest\Request;

trait UsesResourceModel
{

    /**
     * Get FieldParser rules.
     *
     * @return array
     */
    protected function getFieldParserRules(): array
    {
        if ($this->apiService->getConfig('request.meta.enabled') === true
            && in_array(App::environment(), $this->apiService->getConfig('request.meta.env', []))) {

            return [
                'fields' => 'isString',
                $this->apiService->getConfig('request.meta.field', 'meta') => 'isString'
            ];

        }

        return [
            'fields' => 'isString'
        ];

    }

    /**
     * Get QueryParser rules.
     *
     * @return array
     */
    protected function getQueryParserRules(): array
    {
        return [
            'filter' => 'isJson',
            'aggregate' => 'isJson'
        ];
    }

    /*
     * Exists in ApiController
     */
    protected abstract function validateFieldsExist(array $array, array $keys): void;

    protected abstract function validateFieldsDoNotExist(array $array, array $keys): void;

    protected abstract function getJsonBody(array $rules = [], bool $allow_other = false): array;

    /**
     * Get only and validate writable fields from body.
     *
     * Optionally ensure all required fields exist (on create).
     *
     * Optionally ensure predefined values do not exist, then set their value.
     * Helpful when values are set by path parameters.
     *
     * Optionally ensure disallowed fields do not exist (on update).
     * Helpful for scoped resources whose scoped values are set by path parameters.
     *
     * @param ResourceModel $resourceModel
     * @param bool $validate_required_fields
     * @param array $defined_values (Predefined values not allowed to be defined in body)
     * @param array $disallowed_fields
     * @return array
     * @throws BadRequestException
     */
    protected function getResourceBody(ResourceModel $resourceModel, bool $validate_required_fields = false, array $defined_values = [], array $disallowed_fields = []): array
    {

        $body = $this->getJsonBody($resourceModel->getAllowedFieldsWrite());

        if (!empty($defined_values)) {
            $this->validateFieldsDoNotExist($body, array_keys($defined_values));
            $body = array_merge($body, $defined_values);
        }

        if ($validate_required_fields === true) {
            $this->validateFieldsExist($body, $resourceModel->getRequiredFields());
        }

        if (!empty($disallowed_fields)) {
            foreach ($disallowed_fields as $field) {
                if (isset($body[$field])) {
                    throw new BadRequestException('Unable to get body: Invalid field (' . $field . ')');
                }
            }
        }

        return $body;

    }

    /**
     * Create new ResourceModel resource.
     *
     * @param ResourceModel $resourceModel
     * @param array $fields
     * @return array (Created resource)
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     */
    protected function createResource(ResourceModel $resourceModel, array $fields): array
    {

        try {

            return $resourceModel->create($fields)->read();

        } catch (AlreadyExistsException $e) {
            throw new ConflictException('Unable to create resource: Existing conflict', 0, $e);
        } catch (DoesNotExistException|InvalidFieldException $e) {
            throw new BadRequestException('Unable to create resource: Invalid or missing field(s)', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to create resource: Unexpected error', 0, $e);
        }

    }

    /**
     * List ResourceModel resources, including pagination and aggregate of requested.
     *
     * Returned array keys:
     * - list: Collection list
     * - config: Schema configuration array with pagination and aggregate data
     *
     * @param ResourceModel $resourceModel
     * @param array $query_filter (Additional filters to apply to query)
     * @return array
     * @throws ApiServiceException
     * @throws BadRequestException
     */
    protected function listResources(ResourceModel $resourceModel, array $query_filter = []): array
    {

        $query = Request::getQuery();

        if (!empty($query_filter)) {

            if (isset($query['filter'])) {

                $filter = json_decode($query['filter'], true);

                if ($filter) {
                    $query['filter'] = json_encode(array_merge($filter, $query_filter));
                } else {
                    $query['filter'] = json_encode($query_filter);
                }

            } else {
                $query['filter'] = json_encode($query_filter);
            }

        }

        try {
            $parser = new QueryParser($query);
            $collection = $resourceModel->list($parser);
        } catch (InvalidRequestException $e) {
            throw new BadRequestException('Unable to list resource: Invalid request', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to list resource: Unexpected error', 0, $e);
        }

        try {
            $pagination = $collection->getPagination();
            $aggregate = $collection->getAggregate();
        } catch (InvalidRequestException $e) {
            throw new BadRequestException('Unable to list resource: Invalid request', 0, $e);
        }

        $result = [
            'list' => $collection->list(),
            'config' => []
        ];

        if ($parser->getPagination() != '' && !empty($pagination)) {
            $result['config']['pagination_type'] = $parser->getPagination();
            $result['config']['pagination'] = $pagination;
        }

        if (!empty($aggregate)) {
            $result['config']['aggregate'] = $aggregate;
        }

        return $result;

    }

    /**
     * Read ResourceModel resource.
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @return array
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    protected function readResource(ResourceModel $resourceModel, mixed $primary_key_id): array
    {

        $parser = new FieldParser(Request::getQuery());

        try {

            return $resourceModel->read($primary_key_id, $parser->getFields());

        } catch (DoesNotExistException $e) {
            throw new NotFoundException('Unable to read resource: Resource does not exist', 0, $e);
        } catch (InvalidRequestException $e) {
            throw new BadRequestException('Unable to read resource: Invalid field(s)', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to read resource: Unexpected error', 0, $e);
        }

    }

    /**
     * Read filtered ResourceModel resource.
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @param array $filtered_fields (Key = field, value = required resource value)
     * @return array
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    protected function readFilteredResource(ResourceModel $resourceModel, mixed $primary_key_id, array $filtered_fields = []): array
    {

        /*
         * This queries all readable fields from the database to ensure
         * the $filtered_fields array key exists, then only returns the
         * fields requested via $parser->getFields().
         */

        try {

            $resource = $resourceModel->read($primary_key_id);

        } catch (DoesNotExistException $e) {
            throw new NotFoundException('Unable to read resource: Resource does not exist', 0, $e);
        } catch (InvalidRequestException $e) {
            throw new BadRequestException('Unable to read resource: Invalid field(s)', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to read resource: Unexpected error', 0, $e);
        }

        foreach ($filtered_fields as $field => $value) {

            if (Arr::get($resource, $field) != $value) {
                throw new NotFoundException('Unable to read resource: Resource does not exist');
            }

        }

        $parser = new FieldParser(Request::getQuery());

        return Arr::only($resource, $parser->getFields());

    }

    /**
     * Update ResourceModel resource.
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @param array $fields
     * @return array
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    protected function updateResource(ResourceModel $resourceModel, mixed $primary_key_id, array $fields): array
    {

        try {

            return $resourceModel->update($primary_key_id, $fields)->read();

        } catch (AlreadyExistsException $e) {
            throw new ConflictException('Unable to update resource: Existing conflict', 0, $e);
        } catch (DoesNotExistException $e) {
            throw new NotFoundException('Unable to update resource: Resource does not exist', 0, $e);
        } catch (InvalidFieldException $e) {
            throw new BadRequestException('Unable to update resource: Invalid or missing field(s)', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to update resource: Unexpected error', 0, $e);
        }

    }

    /**
     * Delete ResourceModel resource.
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @return bool
     * @throws ApiServiceException
     */
    protected function deleteResource(ResourceModel $resourceModel, mixed $primary_key_id): bool
    {

        try {
            return $resourceModel->delete($primary_key_id);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to delete resource: Unexpected error', 0, $e);
        }

    }

    /**
     * Does filtered resource exist?
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key
     * @param array $query_filter
     * @return bool
     * @throws ApiServiceException
     */
    protected function filteredResourceExists(ResourceModel $resourceModel, mixed $primary_key, array $query_filter = []): bool
    {

        $query_filter = array_merge($query_filter, [
            [
                $resourceModel->getPrimaryKey() => [
                    'eq' => $primary_key
                ]
            ]
        ]);

        $query = [
            'fields' => $resourceModel->getPrimaryKey(),
            'filter' => $query_filter,
            'limit' => 1
        ];

        try {
            $result = $resourceModel->list(new QueryParser($query), true);
        } catch (OrmServiceException $e) {
            throw new ApiServiceException($e->getmessage());
        }

        return $result->getCount() > 0;

    }

}