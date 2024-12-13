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
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantUserMetaModel;

class TenantUserMeta extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantUserMetaModel $tenantUserMetaModel;

    public function __construct(ApiService $apiService, TenantUserMetaModel $tenantUserMetaModel)
    {
        parent::__construct($apiService);
        $this->tenantUserMetaModel = $tenantUserMetaModel;
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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

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

        $this->validateCanRead($params['tenant'], $params['tenant_user']);

        $this->validateQuery($this->getQueryParserRules(), true);

        $query_filter = [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantUserMetaModel, $query_filter);

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

        $this->validateCanRead($params['tenant'], $params['tenant_user']);

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->tenantUserMetaModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantUserMetaModel, $params['id']);

        $this->respond(200, TenantUserMetaResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function update(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'tenant_user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if (!$this->filteredResourceExists($this->tenantUserMetaModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $body = $this->getResourceBody($this->tenantUserMetaModel, false, [
            'tenant_user' => $params['tenant_user']
        ]);

        $resource = $this->updateResource($this->tenantUserMetaModel, $params['id'], $body);

        $this->respond(200, TenantUserMetaResource::create($resource));

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

        if ($this->filteredResourceExists($this->tenantUserMetaModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantUserMetaModel, $params['id']);
        }

        $this->respond(204);

    }

}