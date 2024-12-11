<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\TenantRolePermissionCollection;
use Bayfront\BonesService\Api\Schemas\TenantRolePermissionResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;

class TenantRolePermissions extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantRolePermissionsModel $tenantRolePermissionsModel;

    public function __construct(ApiService $apiService, TenantRolePermissionsModel $tenantRolePermissionsModel)
    {
        parent::__construct($apiService);
        $this->tenantRolePermissionsModel = $tenantRolePermissionsModel;
    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws ForbiddenException
     */
    public function create(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'roles:update',
            'roles.read'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantRolePermissionsModel, true, [
            'role' => $params['role']
        ]);

        $resource = $this->createResource($this->tenantRolePermissionsModel, $body);

        $this->respond(201, TenantRolePermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'roles:read'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $query_filter = [
            [
                'role' => [
                    'eq' => $params['role']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantRolePermissionsModel, $query_filter);

        $this->respond(200, TenantRolePermissionCollection::create($collection['list'], $collection['config']));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'roles:read'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->tenantRolePermissionsModel, $params['id'], [
            [
                'role' => [
                    'eq' => $params['role']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantRolePermissionsModel, $params['id']);

        $this->respond(200, TenantRolePermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {
        // Non-routed (relationship)
    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function delete(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'roles:update'
        ]);

        if ($this->filteredResourceExists($this->tenantRolePermissionsModel, $params['id'], [
            [
                'role' => [
                    'eq' => $params['role']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantRolePermissionsModel, $params['id']);
        }

        $this->respond(204);

    }

}