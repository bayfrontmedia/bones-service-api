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
use Bayfront\BonesService\Api\Schemas\PermissionCollection;
use Bayfront\BonesService\Api\Schemas\PermissionResource;
use Bayfront\BonesService\Api\Schemas\TenantCollection;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantsModel;

class Permissions extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected PermissionsModel $permissionsModel;

    /**
     * @param ApiService $apiService
     * @param PermissionsModel $permissionsModel
     * @throws ApiServiceException
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService, PermissionsModel $permissionsModel)
    {
        parent::__construct($apiService);
        $this->permissionsModel = $permissionsModel;
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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateIsAdmin($this->user);

        $body = $this->getResourceBody($this->permissionsModel, true);

        $resource = $this->createResource($this->permissionsModel, $body);

        $this->respond(201, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function list(array $params): void
    {

        $this->validateQuery($this->getQueryParserRules());

        $this->validateIsAdmin($this->user);

        $collection = $this->listResources($this->permissionsModel);

        $this->respond(200, PermissionCollection::create($collection['list'], $collection['config']));

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
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $this->validateIsAdmin($this->user);

        $resource = $this->readResource($this->permissionsModel, $params['id']);

        $this->respond(200, PermissionResource::create($resource));

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
            'id' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateIsAdmin($this->user);

        $body = $this->getResourceBody($this->permissionsModel);

        $resource = $this->updateResource($this->permissionsModel, $params['id'], $body);

        $this->respond(200, PermissionResource::create($resource));

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
            'id' => 'required|uuid'
        ]);

        $this->validateIsAdmin($this->user);

        $this->deleteResource($this->permissionsModel, $params['id']);

        $this->respond(204);

    }

    /**
     * List tenants with permission.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listTenants(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateIsAdmin($this->user);

        // Ensure permission exists

        if (!$this->resourceExists($this->permissionsModel, $params['id'])) {
            throw new NotFoundException();
        }

        // Get array of permission ID's

        $tenantPermissionsModel = new TenantPermissionsModel($this->rbacService);

        try {

            $tenantCollection = $tenantPermissionsModel->list(new QueryParser([
                'fields' => 'tenant',
                'filter' => [
                    [
                        'permission' => [
                            'eq' => $params['id']
                        ]
                    ]
                ]
            ]), true);

        } catch (InvalidRequestException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $tenant_ids = Arr::pluck($tenantCollection->list(), 'tenant');

        // List tenants

        $tenantsModel = new TenantsModel($this->rbacService);

        $query_filter = [
            [
                'id' => [
                    'in' => implode(',', $tenant_ids)
                ]
            ]
        ];

        $collection = $this->listResources($tenantsModel, $query_filter);

        $this->respond(200, TenantCollection::create($collection['list'], $collection['config']));

    }

}