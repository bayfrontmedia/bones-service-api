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
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantMetaModel;

class TenantMeta extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, UsesResourceModel;

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:create'
        ]);

        $body = $this->getResourceBody($this->tenantMetaModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantMetaModel, $body);

        $this->respond(201, TenantMetaResource::create($resource));

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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:read'
        ]);

        $collection = $this->listTenantResources($this->tenantMetaModel, $params['tenant']);

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

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantResourceExists($this->tenantMetaModel, $params['tenant'], $params['id']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:read'
        ]);

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantResourceExists($this->tenantMetaModel, $params['tenant'], $params['id']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_meta:update'
        ]);

        $body = $this->getResourceBody($this->tenantMetaModel, false, [], [
            'tenant'
        ]);

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

        if ($this->tenantResourceExists($this->tenantMetaModel, $params['tenant'], $params['id'])) {
            $this->deleteResource($this->tenantMetaModel, $params['id']);
        }

        $this->respond(204);

    }

}