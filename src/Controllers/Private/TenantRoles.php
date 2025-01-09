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
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\TenantRoleCollection;
use Bayfront\BonesService\Api\Schemas\TenantRoleResource;
use Bayfront\BonesService\Api\Schemas\TenantUserCollection;
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUserRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;

class TenantRoles extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, UsesResourceModel;

    protected TenantRolesModel $tenantRolesModel;

    /**
     * @param ApiService $apiService
     * @param TenantRolesModel $tenantRolesModel
     * @throws ApiServiceException
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService, TenantRolesModel $tenantRolesModel)
    {
        parent::__construct($apiService);
        $this->tenantRolesModel = $tenantRolesModel;
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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:create'
        ]);

        $body = $this->getResourceBody($this->tenantRolesModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantRolesModel, $body);

        $this->respond(201, TenantRoleResource::create($resource));

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

        try {

            if ($this->user->canDoAll($params['tenant'], [
                'tenant_roles:read'
            ])) {

                $query_filter = [
                    [
                        'tenant' => [
                            'eq' => $params['tenant']
                        ]
                    ]
                ];

            } else {

                $query_filter = [
                    [
                        'tenant' => [
                            'eq' => $params['tenant']
                        ]
                    ],
                    [
                        'id' => [
                            'in' => implode(',', Arr::pluck($this->user->getRoles($params['tenant']), 'id'))
                        ]
                    ]
                ];

            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $collection = $this->listResources($this->tenantRolesModel, $query_filter);

        $this->respond(200, TenantRoleCollection::create($collection['list'], $collection['config']));

    }

    /**
     * Validate user can read role.
     *
     * @param string $tenant
     * @param string $role_id
     * @return void
     * @throws ApiServiceException
     * @throws ForbiddenException
     */
    private function validateCanRead(string $tenant, string $role_id): void
    {

        try {

            if (!$this->user->canDoAll($tenant, [
                    'tenant_roles:read'
                ]) && !$this->user->hasRole($tenant, $role_id)) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

    }

    /**
     * @inheritDoc
     * @param array $params
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

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['id']);

        $this->validateCanRead($params['tenant'], $params['id']);

        $resource = $this->readResource($this->tenantRolesModel, $params['id']);

        $this->respond(200, TenantRoleResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function update(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['id']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:update'
        ]);

        $body = $this->getResourceBody($this->tenantRolesModel, false, [], [
            'tenant'
        ]);

        $resource = $this->updateResource($this->tenantRolesModel, $params['id'], $body);

        $this->respond(200, TenantRoleResource::create($resource));

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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:delete'
        ]);

        if ($this->tenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['id'])) {
            $this->deleteResource($this->tenantRolesModel, $params['id']);
        }

        $this->respond(204);

    }

    /**
     * List tenant users who have role.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listUsers(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        // Ensure tenant role exists

        $this->validateTenantResourceExists($this->tenantRolesModel, $params['tenant'], $params['id']);

        $this->validateCanRead($params['tenant'], $params['id']);

        // Get array of tenant user ID's

        $tenantUserRolesModel = new TenantUserRolesModel($this->rbacService);

        try {

            $rolesCollection = $tenantUserRolesModel->list(new QueryParser([
                'fields' => 'tenant_user',
                'filter' => [
                    [
                        'role' => [
                            'eq' => $params['id']
                        ]
                    ]
                ]
            ]), true);

        } catch (InvalidRequestException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        /** @noinspection DuplicatedCode */
        $user_ids = Arr::pluck($rolesCollection->list(), 'tenant_user');

        // List users

        $tenantUsersModel = new TenantUsersModel($this->rbacService);

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ],
            [
                'id' => [
                    'in' => implode(',', $user_ids)
                ]
            ]
        ];

        $collection = $this->listResources($tenantUsersModel, $query_filter);

        $this->respond(200, TenantUserCollection::create($collection['list'], $collection['config']));

    }

}