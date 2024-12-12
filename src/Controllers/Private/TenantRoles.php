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
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

class TenantRoles extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:create'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
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
     */
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid'
        ]);

        $query_filter = [];

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                'tenant_roles:read'
            ])) {

                $query_filter = [
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

        $this->validateQuery($this->getQueryParserRules());

        $collection = $this->listResources($this->tenantRolesModel, $query_filter);

        $this->respond(200, TenantRoleCollection::create($collection['list'], $collection['config']));

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

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_roles:read'
                ]) && !$this->user->hasRole($params['tenant'], $params['id'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->tenantRolesModel, $params['id']);

        $this->respond(200, TenantRoleResource::create($resource));

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
            'id' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_roles:update'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantRolesModel, false, [
            'tenant' => $params['tenant']
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

        $this->deleteResource($this->tenantRolesModel, $params['id']);

        $this->respond(204);

    }

}