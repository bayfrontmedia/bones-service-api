<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Models\ResourceModel;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;

trait UsesOrmModel
{

    /**
     * Create new OrmResource.
     *
     * Array keys:
     * - data: Created resource
     *
     * @param ResourceModel $resourceModel
     * @param array $fields
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function createOrmResource(ResourceModel $resourceModel, array $fields): array
    {

        try {

            return [
                'data' => $resourceModel->create($fields)
            ];

        } catch (AlreadyExistsException $e) {
            throw new ConflictException('Unable to create resource: Existing conflict', 0, $e);
        } catch (DoesNotExistException|InvalidFieldException $e) {
            throw new BadRequestException('Unable to create resource: Invalid or missing field(s)', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to create resource: Unexpected error', 0, $e);
        }

    }

    /**
     * List OrmModel resources, including pagination and aggregate of requested.
     *
     * Array keys:
     * - data: Collection list
     * - pagination: Collection pagination, if requested
     * - aggregate: Aggregate results, if requested
     *
     * @param ResourceModel $resourceModel
     * @param array $query (URL query parameters)
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function listOrmResources(ResourceModel $resourceModel, array $query = []): array
    {

        try {
            $collection = $resourceModel->list(new QueryParser($query));
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
            'data' => $collection->list()
        ];

        if (!empty($pagination)) {
            $result['pagination'] = $pagination;
        }

        if (!empty($aggregate)) {
            $result['aggregate'] = $aggregate;
        }

        return $result;

    }

    /**
     * Read OrmModel resource.
     *
     * Array keys:
     * - data: Resource
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function readOrmResource(ResourceModel $resourceModel, mixed $primary_key_id): array
    {

        try {

            return [
                'data' => $resourceModel->read($primary_key_id)
            ];

        } catch (DoesNotExistException $e) {
            throw new NotFoundException('Unable to read resource: Resource does not exist', 0, $e);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to read resource: Unexpected error', 0, $e);
        }

    }

    /**
     * Update OrmModel resource.
     *
     * Array keys:
     * - data: Updated resource
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @param array $fields
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function updateOrmResource(ResourceModel $resourceModel, mixed $primary_key_id, array $fields): array
    {

        try {

            return [
                'data' => $resourceModel->update($primary_key_id, $fields)
            ];

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
     * Delete single OrmModel resource.
     *
     * @param ResourceModel $resourceModel
     * @param mixed $primary_key_id
     * @return bool
     * @throws ApiServiceException
     */
    protected function deleteOrmResource(ResourceModel $resourceModel, mixed $primary_key_id): bool
    {

        try {
            return $resourceModel->delete($primary_key_id);
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to delete resource: Unexpected error', 0, $e);
        }

    }

}