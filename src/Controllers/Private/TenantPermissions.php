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
use Bayfront\BonesService\Api\Schemas\TenantPermissionResouce;
use Bayfront\BonesService\Api\Schemas\TenantRoleCollection;
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

class TenantPermissions extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, UsesResourceModel;

    protected TenantPermissionsModel $tenantPermissionsModel;

    public function __construct(ApiService $apiService, TenantPermissionsModel $tenantPermissionsModel)
    {
        parent::__construct($apiService);
        $this->tenantPermissionsModel = $tenantPermissionsModel;
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

        $body = $this->getResourceBody($this->tenantPermissionsModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantPermissionsModel, $body);

        $this->respond(201, TenantPermissionResouce::create($resource));

    }

    /**
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateTenantExists($params['tenant']);

        $query_filter = $this->getReadQueryFilter($params['tenant']);

        $collection = $this->listResources($this->tenantPermissionsModel, $query_filter);

        $this->respond(200, TenantPermissionCollection::create($collection['list'], $collection['config']));

    }

    /**
     * Get query filter to read permissions.
     *
     * @param string $tenant_id
     * @return array
     * @throws ApiServiceException
     */
    private function getReadQueryFilter(string $tenant_id): array
    {

        try {

            if ($this->user->canDoAll($tenant_id, [
                'tenant_permissions:read'
            ])) {

                return [
                    [
                        'tenant' => [
                            'eq' => $tenant_id
                        ]
                    ]
                ];

            } else {

                return [
                    [
                        'tenant' => [
                            'eq' => $tenant_id
                        ]
                    ],
                    [
                        'permission' => [
                            'in' => implode(',', Arr::pluck($this->user->getPermissions($tenant_id), 'id'))
                        ]
                    ]
                ];

            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantResourceExists($this->tenantPermissionsModel, $params['tenant'], $params['id']);

        $query_filter = $this->getReadQueryFilter($params['tenant']);

        if (!$this->resourceExists($this->tenantPermissionsModel, $params['id'], $query_filter)) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantPermissionsModel, $params['id']);

        $this->respond(200, TenantPermissionResouce::create($resource));

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
            'id' => 'required|uuid'
        ]);

        $this->validateIsAdmin($this->user);

        if ($this->tenantResourceExists($this->tenantPermissionsModel, $params['tenant'], $params['id'])) {
            $this->deleteResource($this->tenantPermissionsModel, $params['id']);
        }

        $this->respond(204);

    }


    /**
     * List roles with tenant permission.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listRoles(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        // Ensure tenant permission exists

        $this->validateTenantResourceExists($this->tenantPermissionsModel, $params['tenant'], $params['id']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_permissions:read',
            'tenant_roles:read'
        ]);

        // Get array of role ID's

        $tenantRolePermissionsModel = new TenantRolePermissionsModel($this->rbacService);

        try {

            $rolePermissionsCollection = $tenantRolePermissionsModel->list(new QueryParser([
                'fields' => 'role',
                'filter' => [
                    [
                        'tenant_permission' => [
                            'eq' => $params['id']
                        ]
                    ]
                ]
            ]), true);

        } catch (InvalidRequestException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $role_ids = Arr::pluck($rolePermissionsCollection->list(), 'role');

        // List roles

        $tenantRolesModel = new TenantRolesModel($this->rbacService);

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ],
            [
                'id' => [
                    'in' => implode(',', $role_ids)
                ]
            ]
        ];

        $collection = $this->listResources($tenantRolesModel, $query_filter);

        $this->respond(200, TenantRoleCollection::create($collection['list'], $collection['config']));

    }

}