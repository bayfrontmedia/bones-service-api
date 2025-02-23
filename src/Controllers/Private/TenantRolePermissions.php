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
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

class TenantRolePermissions extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, UsesResourceModel;

    protected TenantRolesModel $tenantRolesModel;
    protected TenantRolePermissionsModel $tenantRolePermissionsModel;

    public function __construct(ApiService $apiService, TenantRolesModel $tenantRolesModel, TenantRolePermissionsModel $tenantRolePermissionsModel)
    {
        parent::__construct($apiService);
        $this->tenantRolesModel = $tenantRolesModel;
        $this->tenantRolePermissionsModel = $tenantRolePermissionsModel;
    }

    /**
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function create(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['role']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:update',
            'tenant_permissions:read'
        ]);

        $body = $this->getResourceBody($this->tenantRolePermissionsModel, true, [
            'role' => $params['role']
        ]);

        if (isset($body['permission']) && !$this->tenantResourceExists(new TenantPermissionsModel($this->rbacService), $params['tenant'], $body['permission'])) {
            throw new BadRequestException('Invalid permission');
        }

        $resource = $this->createResource($this->tenantRolePermissionsModel, $body);

        $this->respond(201, TenantRolePermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['role']);

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_roles:read',
                    'tenant_permissions:read'
                ]) && !$this->user->hasRole($params['tenant'], $params['role'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

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

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['role']);

        if (!$this->resourceExists($this->tenantRolePermissionsModel, $params['id'], [
            [
                'role' => [
                    'eq' => $params['role']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_roles:read',
                    'tenant_permissions:read'
                ]) && !$this->user->hasRole($params['tenant'], $params['role'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
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
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function delete(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'role' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['role']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:update'
        ]);

        if ($this->resourceExists($this->tenantRolePermissionsModel, $params['id'], [
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