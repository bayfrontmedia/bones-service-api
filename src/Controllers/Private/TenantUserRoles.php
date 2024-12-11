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
use Bayfront\BonesService\Api\Schemas\TenantUserRoleCollection;
use Bayfront\BonesService\Api\Schemas\TenantUserRoleResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantUserRolesModel;

class TenantUserRoles extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantUserRolesModel $tenantUserRolesModel;

    public function __construct(ApiService $apiService, TenantUserRolesModel $tenantUserRolesModel)
    {
        parent::__construct($apiService);
        $this->tenantUserRolesModel = $tenantUserRolesModel;
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
            'tenant_user' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:update',
            'tenant_users:update',
            'tenant_roles:read'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantUserRolesModel, true, [
            'tenant_user' => $params['tenant_user']
        ]);

        $resource = $this->createResource($this->tenantUserRolesModel, $body);

        $this->respond(201, TenantUserRoleResource::create($resource));

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
            'tenant_user' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:read'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $query_filter = [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantUserRolesModel, $query_filter);

        $this->respond(200, TenantUserRoleCollection::create($collection['list'], $collection['config']));

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
            'tenant_user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:read'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->tenantUserRolesModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantUserRolesModel, $params['id']);

        $this->respond(200, TenantUserRoleResource::create($resource));

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
            'tenant_user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:update',
            'tenant_users:update'
        ]);

        if ($this->filteredResourceExists($this->tenantUserRolesModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantUserRolesModel, $params['id']);
        }

        $this->respond(204);

    }

}