<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiControllerInterface;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

/**
 * TODO:
 * All tenant-scoped controllers need to check
 * if user is not admin, user must belong to tenant and tenant must be enabled.
 *
 * $this->requirePermissions($user, [], $tenant = '')
 *
 *
 * If user is not admin, do not allow tenant ID to be defined in body
 * of create() and update()
 *
 * Possibly update getBody to getBody($validations)
 */
class TenantRoles extends PrivateApiController implements ApiControllerInterface
{

    protected TenantRolesModel $tenantRolesModel;

    public function __construct(ApiService $apiService, TenantRolesModel $tenantRolesModel)
    {
        parent::__construct($apiService);
        $this->tenantRolesModel = $tenantRolesModel;
    }

    /**
     * @inheritDoc
     */
    public function create(array $params): void
    {

        // Check permissions
        try {

            if (!$this->user->canDoAll(Arr::get($params, 'tenant_id', ''), [
                'roles.create',
                'roles.read'
            ])) {
                $this->abort(403, 'Unable to create resource: Insufficient permissions');
            }

        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to create resource: Unexpected error', $e);
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Validate body

        // Function
        try {
            $resource = $this->tenantRolesModel->create($this->getBody());
        } catch (AlreadyExistsException $e) {
            $this->abort(409, 'Unable to create resource: Existing conflict', $e);
        } catch (DoesNotExistException|InvalidFieldException $e) {
            $this->abort(400, 'Unable to create resource: Invalid or missing field(s)', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to create resource: Unexpected error', $e);
        }

        // Schema
        $schema = [
            'data' => $resource->read()
        ];

        // Response
        $this->respond(201, $schema);

    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {

        // Check permissions
        try {

            if (!$this->user->canDoAll(Arr::get($params, 'tenant_id', ''), [
                'roles.read'
            ])) {
                $this->abort(403, 'Unable to list resource: Insufficient permissions');
            }

        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to list resource: Unexpected error', $e);
        }

        // Require headers

        // Validate body

        // Function
        try {
            $collection = $this->tenantRolesModel->list(new QueryParser($this->getQuery()));
        } catch (InvalidRequestException $e) {
            $this->abort(400, 'Unable to list resource: Invalid request', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to list resource: Unexpected error', $e);
        }

        try {
            $pagination = $collection->getPagination();
            $aggregate = $collection->getAggregate();
        } catch (InvalidRequestException $e) {
            $this->abort(400, 'Unable to list resource: Invalid request', $e);
        }

        if (empty($pagination) && empty($aggregate)) {

            // Schema
            $schema = $collection->list();

        } else {

            // Schema
            $schema = [
                'data' => $collection->list()
            ];

            if (!empty($pagination)) {
                $schema['pagination'] = $pagination;
            }

            if (!empty($aggregate)) {
                $schema['aggregate'] = $aggregate;
            }

        }

        // Response
        $this->respond(200, $schema, [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {

        // Check permissions
        try {

            if (!$this->user->canDoAll(Arr::get($params, 'tenant_id', ''), [
                'roles.read'
            ])) {
                $this->abort(403, 'Unable to read resource: Insufficient permissions');
            }

        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to read resource: Unexpected error', $e);
        }

        // Require headers

        // Validate body

        // Function
        try {
            $resource = $this->tenantRolesModel->read(Arr::get($params, 'id', ''));
        } catch (DoesNotExistException $e) {
            $this->abort(404, 'Unable to read resource: Resource does not exist', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to read resource: Unexpected error', $e);
        }

        // Schema
        $schema = [
            'data' => $resource
        ];

        // Response
        $this->respond(200, $schema, [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {

        // Check permissions
        try {

            if (!$this->user->canDoAll(Arr::get($params, 'tenant_id', ''), [
                'roles.update',
                'roles.read'
            ])) {
                $this->abort(403, 'Unable to update resource: Insufficient permissions');
            }

        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to update resource: Unexpected error', $e);
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Validate body

        // Function
        try {
            $resource = $this->tenantRolesModel->update(Arr::get($params, 'id'), $this->getBody());
        } catch (AlreadyExistsException $e) {
            $this->abort(409, 'Unable to update resource: Existing conflict', $e);
        } catch (DoesNotExistException $e) {
            $this->abort(404, 'Unable to update resource: Resource does not exist', $e);
        } catch (InvalidFieldException $e) {
            $this->abort(400, 'Unable to update resource: Invalid or missing field(s)', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to update resource: Unexpected error', $e);
        }

        // Schema
        $schema = [
            'data' => $resource->read()
        ];

        // Response
        $this->respond(200, $schema);

    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {

        // Check permissions
        try {

            if (!$this->user->canDoAll(Arr::get($params, 'tenant_id', ''), [
                'roles.delete'
            ])) {
                $this->abort(403, 'Unable to delete resource: Insufficient permissions');
            }

        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to delete resource: Unexpected error', $e);
        }

        // Require headers

        // Validate body

        // Function
        try {
            $this->tenantRolesModel->delete(Arr::get($params, 'id'));
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to delete resource: Unexpected error', $e);
        }

        // Response
        $this->respond(204);

    }

}