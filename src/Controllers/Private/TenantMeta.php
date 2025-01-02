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
use Bayfront\BonesService\Api\Schemas\TenantMetaCollection;
use Bayfront\BonesService\Api\Schemas\TenantMetaResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantMetaModel;

class TenantMeta extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantMetaModel $tenantMetaModel;

    public function __construct(ApiService $apiService, TenantMetaModel $tenantMetaModel)
    {
        parent::__construct($apiService);
        $this->tenantMetaModel = $tenantMetaModel;
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
            'tenant_meta:create'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantMetaModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantMetaModel, $body);

        $this->respond(201, TenantMetaResource::create($resource));

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
            'tenant' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:read'
        ]);

        $this->validateQuery($this->getQueryParserRules(), true);

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantMetaModel, $query_filter);

        $this->respond(200, TenantMetaCollection::create($collection['list'], $collection['config']));

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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:read'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->tenantMetaModel, $params['id'], [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantMetaModel, $params['id']);

        $this->respond(200, TenantMetaResource::create($resource));

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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:update'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantMetaModel, false, [], [
            'tenant'
        ]);

        if (!$this->filteredResourceExists($this->tenantMetaModel, $params['id'], [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->updateResource($this->tenantMetaModel, $params['id'], $body);

        $this->respond(200, TenantMetaResource::create($resource));

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
            'tenant_meta:delete'
        ]);

        if ($this->filteredResourceExists($this->tenantMetaModel, $params['id'], [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantMetaModel, $params['id']);
        }

        $this->respond(204);

    }

}