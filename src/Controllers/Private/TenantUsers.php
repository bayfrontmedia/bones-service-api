<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\TenantPermissionCollection;
use Bayfront\BonesService\Api\Schemas\TenantUserCollection;
use Bayfront\BonesService\Api\Schemas\TenantUserResource;
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantsModel;
use Bayfront\BonesService\Rbac\Models\TenantUserRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;

class TenantUsers extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, UsesResourceModel;

    protected TenantUsersModel $tenantUsersModel;

    public function __construct(ApiService $apiService, TenantUsersModel $tenantUsersModel)
    {
        parent::__construct($apiService);
        $this->tenantUsersModel = $tenantUsersModel;
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
            'tenant' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateIsAdmin($this->user);

        $body = $this->getResourceBody($this->tenantUsersModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantUsersModel, $body);

        $this->respond(201, TenantUserResource::create($resource));

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
            'tenant' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateTenantExists($params['tenant']);

        try {

            if (!$this->user->isAdmin() && !$this->user->inTenant($params['tenant'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantUsersModel, $query_filter);

        $this->respond(200, TenantUserCollection::create($collection['list'], $collection['config']));

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
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantResourceExists($this->tenantUsersModel, $params['tenant'], $params['id']);

        try {

            if (!$this->user->isAdmin() && !$this->user->inTenant($params['tenant'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $resource = $this->readResource($this->tenantUsersModel, $params['id']);

        $this->respond(200, TenantUserResource::create($resource));

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
     */
    public function delete(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_users:delete'
                ]) && $this->user->getTenantUserId($params['tenant']) !== $params['id']) {

                throw new ForbiddenException();

            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        if ($this->tenantResourceExists($this->tenantUsersModel, $params['tenant'], $params['id'])) {
            $this->deleteResource($this->tenantUsersModel, $params['id']);
        }

        $this->respond(204);

    }

    /**
     * List tenant user permissions.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listPermissions(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        // Ensure tenant user exists

        $this->validateTenantResourceExists($this->tenantUsersModel, $params['tenant'], $params['id']);

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_permissions:read'
                ]) && $this->user->getTenantUserId($params['tenant']) !== $params['id']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $tenantsModel = new TenantsModel($this->rbacService);

        try {

            $tenant_user = $this->tenantUsersModel->read($params['id']); // Resource
            $tenant_owner = $tenantsModel->getOwnerId($params['tenant']); // String (user id)

        } catch (DoesNotExistException) {
            throw new NotFoundException();
        } catch (InvalidRequestException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        if (Arr::get($tenant_user, 'user') == $tenant_owner) { // If tenant user owns tenant

            // List all tenant permissions

            $tenantPermissionsModel = new TenantPermissionsModel($this->rbacService);

            $collection = $this->listResources($tenantPermissionsModel, [
                [
                    'tenant' => [
                        'eq' => $params['tenant']
                    ]
                ]
            ]);

        } else {

            // Get array of role ID's

            $tenantUserRolesModel = new TenantUserRolesModel($this->rbacService);

            try {

                $rolesCollection = $tenantUserRolesModel->list(new QueryParser([
                    'fields' => 'role',
                    'filter' => [
                        [
                            'tenant_user' => [
                                'eq' => $params['id']
                            ]
                        ]
                    ]
                ]), true);

            } catch (InvalidRequestException|UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

            $role_ids = Arr::pluck($rolesCollection->list(), 'role'); // May be empty array

            if (!empty($role_ids)) {

                // Get array of permission ID's

                $tenantRolePermissionsModel = new TenantRolePermissionsModel($this->rbacService);

                try {

                    $rolePermissionsCollection = $tenantRolePermissionsModel->list(new QueryParser([
                        'fields' => 'tenant_permission',
                        'filter' => [
                            [
                                'role' => [
                                    'in' => implode(',', $role_ids)
                                ]
                            ]
                        ]
                    ]), true);

                } catch (InvalidRequestException|UnexpectedException $e) {
                    throw new ApiServiceException($e->getMessage());
                }

                $permission_ids = array_unique(Arr::pluck($rolePermissionsCollection->list(), 'tenant_permission'));

            } else {
                $permission_ids = $role_ids; // Empty array
            }

            // List tenant permissions

            $tenantPermissionsModel = new TenantPermissionsModel($this->rbacService);

            $query_filter = [
                [
                    'id' => [
                        'in' => implode(',', $permission_ids)
                    ]
                ]
            ];

            $collection = $this->listResources($tenantPermissionsModel, $query_filter);

        }

        $this->respond(200, TenantPermissionCollection::create($collection['list'], $collection['config']));

    }

}