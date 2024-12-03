<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Interfaces\ApiControllerInterface;
use Bayfront\BonesService\Api\Traits\ScopedEndpoint;
use Bayfront\BonesService\Api\Traits\UsesOrmModel;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

class TenantRoles extends PrivateApiController implements ApiControllerInterface
{

    use UsesOrmModel, ScopedEndpoint;

    protected TenantRolesModel $tenantRolesModel;

    /**
     * @param ApiService $apiService
     * @param TenantRolesModel $tenantRolesModel
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
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
        $this->requirePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.create',
            'roles.read'
        ]);

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Validate body

        $body = $this->defineScopedFields($this->getBody(), [
            'tenant' => Arr::get($params, 'tenant', '')
        ]);

        // Function

        // Schema
        $schema = $this->createOrmResource($this->tenantRolesModel, $body);

        // Response
        $this->respond(201, $schema);

    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {

        // Check permissions
        $this->requirePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.read'
        ]);

        // Require headers

        // Validate body

        // Function

        // Schema
        $schema = $this->listOrmResources($this->tenantRolesModel, $this->getQuery());

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
        $this->requirePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.read'
        ]);

        // Require headers

        // Validate body

        // Function

        // Schema
        $schema = $this->readOrmResource($this->tenantRolesModel, Arr::get($params, 'id', ''));

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
        $this->requirePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.update',
            'roles.read'
        ]);

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Validate body

        $body = $this->disallowScopedFields($this->getBody(), [
            'tenant'
        ]);

        // Function


        // Schema
        $schema = $this->updateOrmResource($this->tenantRolesModel, Arr::get($params, 'id', ''), $body);

        // Response
        $this->respond(200, $schema);

    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {

        // Check permissions
        $this->requirePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.delete'
        ]);

        // Require headers

        // Validate body

        // Function
        $this->deleteOrmResource($this->tenantRolesModel, Arr::get($params, 'id', ''));

        // Response
        $this->respond(204);

    }

}