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
use Bayfront\BonesService\Api\Schemas\TenantUserMetaCollection;
use Bayfront\BonesService\Api\Schemas\TenantUserMetaResource;
use Bayfront\BonesService\Api\Traits\TenantUserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantUserMetaModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;

class TenantUserMeta extends PrivateApiController implements CrudControllerInterface
{

    use TenantUserResource, UsesResourceModel;

    protected TenantUserMetaModel $tenantUserMetaModel;
    protected TenantUsersModel $tenantUsersModel;

    public function __construct(ApiService $apiService, TenantUserMetaModel $tenantUserMetaModel, TenantUsersModel $tenantUsersModel)
    {
        parent::__construct($apiService);
        $this->tenantUserMetaModel = $tenantUserMetaModel;
        $this->tenantUsersModel = $tenantUsersModel;
    }

    /**
     * Upsert tenant user meta.
     * Returned resource will have a new ID if previously existing.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function upsert(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'tenant_user' => 'required|uuid',
            'key' => 'required'
        ]);

        /** @noinspection DuplicatedCode */
        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        if ($this->apiService->getConfig('tenant.user_meta.manage_self') === true) {

            try {

                if (!$this->user->canDoAll($params['tenant'], [
                        'tenant_user_meta:create'
                    ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                    throw new ForbiddenException();
                }

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else {

            $this->validateHasPermissions($this->user, $params['tenant'], [
                'tenant_user_meta:create'
            ]);

        }

        $body = $this->getResourceBody($this->tenantUserMetaModel, true, [
            'tenant_user' => $params['tenant_user'],
            'meta_key' => $params['key']
        ]);

        try {
            $resource = $this->tenantUserMetaModel->upsert($body);
        } catch (OrmServiceException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->respond(201, TenantUserMetaResource::create($resource->read()));

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

        /** @noinspection DuplicatedCode */
        if ($this->apiService->getConfig('tenant.user_meta.manage_self') === true) {

            try {

                if (!$this->user->canDoAll($params['tenant'], [
                        'tenant_user_meta:create'
                    ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                    throw new ForbiddenException();
                }

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else {

            $this->validateHasPermissions($this->user, $params['tenant'], [
                'tenant_user_meta:create'
            ]);

        }

        $body = $this->getResourceBody($this->tenantUserMetaModel, true, [
            'tenant_user' => $params['tenant_user']
        ]);

        $resource = $this->createResource($this->tenantUserMetaModel, $body);

        $this->respond(201, TenantUserMetaResource::create($resource));

    }

    /**
     * @param string $tenant
     * @param string $tenant_user
     * @return void
     * @throws ApiServiceException
     * @throws ForbiddenException
     */
    private function validateCanRead(string $tenant, string $tenant_user): void
    {

        if ($this->apiService->getConfig('tenant.user_meta.manage_self') === true) {

            try {

                if (!$this->user->canDoAll($tenant, [
                        'tenant_user_meta:read'
                    ]) && $this->user->getTenantUserId($tenant) != $tenant_user) {
                    throw new ForbiddenException();
                }

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else {

            $this->validateHasPermissions($this->user, $tenant, [
                'tenant_user_meta:read'
            ]);

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
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'tenant_user' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateCanRead($params['tenant'], $params['tenant_user']);

        $collection = $this->listTenantUserResources($this->tenantUserMetaModel, $params['tenant_user']);

        $this->respond(200, TenantUserMetaCollection::create($collection['list'], $collection['config']));

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

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateTenantUserResourceExists($this->tenantUserMetaModel, $params['tenant_user'], $params['id']);

        $this->validateCanRead($params['tenant'], $params['tenant_user']);

        $resource = $this->readResource($this->tenantUserMetaModel, $params['id']);

        $this->respond(200, TenantUserMetaResource::create($resource));

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
            'tenant_user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateTenantUserResourceExists($this->tenantUserMetaModel, $params['tenant_user'], $params['id']);

        if ($this->apiService->getConfig('tenant.user_meta.manage_self') === true) {

            try {

                if (!$this->user->canDoAll($params['tenant'], [
                        'tenant_user_meta:update'
                    ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                    throw new ForbiddenException();
                }

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else {

            $this->validateHasPermissions($this->user, $params['tenant'], [
                'tenant_user_meta:update'
            ]);

        }

        $body = $this->getResourceBody($this->tenantUserMetaModel, false, [
            'tenant_user' => $params['tenant_user']
        ]);

        $resource = $this->updateResource($this->tenantUserMetaModel, $params['id'], $body);

        $this->respond(200, TenantUserMetaResource::create($resource));

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

        if ($this->apiService->getConfig('tenant.user_meta.manage_self') === true) {

            try {

                if (!$this->user->canDoAll($params['tenant'], [
                        'tenant_user_meta:delete'
                    ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                    throw new ForbiddenException();
                }

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else {

            $this->validateHasPermissions($this->user, $params['tenant'], [
                'tenant_user_meta:delete'
            ]);

        }

        if ($this->tenantUserResourceExists($this->tenantUserMetaModel, $params['tenant_user'], $params['id'])) {
            $this->deleteResource($this->tenantUserMetaModel, $params['id']);
        }

        $this->respond(204);

    }

}