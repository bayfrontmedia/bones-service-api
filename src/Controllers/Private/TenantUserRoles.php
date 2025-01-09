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
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\TenantUserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUserRolesModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;

class TenantUserRoles extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, TenantUserResource, UsesResourceModel;

    protected TenantUserRolesModel $tenantUserRolesModel;
    protected TenantUsersModel $tenantUsersModel;

    public function __construct(ApiService $apiService, TenantUserRolesModel $tenantUserRolesModel, TenantUsersModel $tenantUsersModel)
    {
        parent::__construct($apiService);
        $this->tenantUserRolesModel = $tenantUserRolesModel;
        $this->tenantUsersModel = $tenantUsersModel;
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
            'tenant_user' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_users:update',
            'tenant_roles:update',
        ]);

        $body = $this->getResourceBody($this->tenantUserRolesModel, true, [
            'tenant_user' => $params['tenant_user']
        ]);

        if (isset($body['role']) && !$this->tenantResourceExists(new TenantRolesModel($this->rbacService), $params['tenant'], $body['role'])) {
            throw new BadRequestException('Invalid role');
        }

        $resource = $this->createResource($this->tenantUserRolesModel, $body);

        $this->respond(201, TenantUserRoleResource::create($resource));

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
            'tenant_user' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_roles:read'
                ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $collection = $this->listTenantUserResources($this->tenantUserRolesModel, $params['tenant_user']);

        $this->respond(200, TenantUserRoleCollection::create($collection['list'], $collection['config']));

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
            'tenant_user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateTenantUserResourceExists($this->tenantUserRolesModel, $params['tenant_user'], $params['id']);

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_roles:read'
                ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
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
            'tenant_user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_users:update',
            'tenant_roles:update'
        ]);

        if ($this->tenantUserResourceExists($this->tenantUserRolesModel, $params['tenant_user'], $params['id'])) {
            $this->deleteResource($this->tenantUserRolesModel, $params['id']);
        }

        $this->respond(204);

    }

}