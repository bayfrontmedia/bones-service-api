<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\PermissionCollection;
use Bayfront\BonesService\Api\Schemas\PermissionResource;
use Bayfront\BonesService\Api\Traits\Auditable;
use Bayfront\BonesService\Api\Traits\UsesOrmModel;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;

class Permissions extends PrivateApiController implements CrudControllerInterface
{

    use Auditable, UsesOrmModel;

    protected PermissionsModel $permissionsModel;

    /**
     * @param ApiService $apiService
     * @param PermissionsModel $permissionsModel
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function __construct(ApiService $apiService, PermissionsModel $permissionsModel)
    {
        parent::__construct($apiService);
        $this->permissionsModel = $permissionsModel;
    }

    /**
     * @inheritDoc
     */
    public function getAuditableFunctions(): array
    {
        return [
            'create',
            'list',
            'read',
            'update',
            'delete'
        ];
    }

    /**
     * @inheritDoc
     */
    public function create(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to create resource: Insufficient permissions');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Validate body

        // Function

        // Schema
        $resource = $this->createOrmResource($this->permissionsModel, $this->getBody());

        // Response
        $this->respond(201, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to list resource: Insufficient permissions');
        }

        // Require headers

        // Validate body

        // Function

        // Schema
        $collection = $this->listOrmResources($this->permissionsModel, $this->getQuery());

        // Response
        $this->respond(200, PermissionCollection::create($collection), [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to read resource: Insufficient permissions');
        }

        // Require headers

        // Validate body

        // Function

        // Schema
        $resource = $this->readOrmResource($this->permissionsModel, Arr::get($params, 'id', ''));

        // Response
        $this->respond(200, PermissionResource::create($resource), [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to update resource: Insufficient permissions');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Validate body

        // Function

        // Schema
        $resource = $this->updateOrmResource($this->permissionsModel, Arr::get($params, 'id', ''), $this->getBody());

        // Response
        $this->respond(200, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to delete resource: Insufficient permissions');
        }

        // Require headers

        // Validate body

        // Function
        $this->deleteOrmResource($this->permissionsModel, Arr::get($params, 'id'));

        // Response
        $this->respond(204);

    }

}