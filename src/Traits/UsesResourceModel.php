<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
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

    /**
     * Create new ResourceModel resource.
     *
     * Array keys:
     * - data: Created resource
     *
     * @param ResourceModel $resourceModel
     * @param array $fields
     * @return array
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
     * Array keys:
     * - list: Collection list
     * - config: Schema configuration array
     *
     * @param ResourceModel $resourceModel
     * @return array
     * @throws ApiServiceException
     * @throws BadRequestException
     */
    protected function listResources(ResourceModel $resourceModel): array
    {

        try {
            $parser = new QueryParser(Request::getQuery());
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
     * Array keys:
     * - data: Resource
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
     * Update ResourceModel resource.
     *
     * Array keys:
     * - data: Updated resource
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

}