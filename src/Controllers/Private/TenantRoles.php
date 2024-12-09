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
use Bayfront\BonesService\Api\Schemas\TenantRolesCollection;
use Bayfront\BonesService\Api\Schemas\TenantRolesResource;
use Bayfront\BonesService\Api\Traits\ScopedEndpoint;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

class TenantRoles extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel, ScopedEndpoint;

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

        $this->validatePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.create',
            'roles.read'
        ]);

        $this->validatePath($params, [
            'tenant' => 'uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->validateScopedFields($this->getResourceBody($this->tenantRolesModel, true), [
            'tenant' => Arr::get($params, 'tenant', '')
        ]);

        $resource = $this->createResource($this->tenantRolesModel, $body);

        $this->respond(201, TenantRolesResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function list(array $params): void
    {

        $this->validatePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.read'
        ]);

        $this->validatePath($params, [
            'tenant' => 'uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $collection = $this->listResources($this->tenantRolesModel);

        $this->respond(200, TenantRolesCollection::create($collection['list'], $collection['config']), [
            'Cache-Control' => 'max-age=3600'
        ]);

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

        $this->validatePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.read'
        ]);

        $this->validatePath($params, [
            'tenant' => 'uuid',
            'id' => 'uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->tenantRolesModel, Arr::get($params, 'id', ''));

        $this->respond(200, TenantRolesResource::create($resource), [
            'Cache-Control' => 'max-age=3600'
        ]);

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

        $this->validatePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.update',
            'roles.read'
        ]);

        $this->validatePath($params, [
            'tenant' => 'uuid',
            'id' => 'uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->validateScopedFields($this->getResourceBody($this->tenantRolesModel), [
            'tenant' => Arr::get($params, 'tenant', '')
        ]);

        $resource = $this->updateResource($this->tenantRolesModel, Arr::get($params, 'id', ''), $body);

        $this->respond(200, TenantRolesResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function delete(array $params): void
    {

        $this->validatePermissions($this->user, Arr::get($params, 'tenant', ''), [
            'roles.delete'
        ]);

        $this->validatePath($params, [
            'tenant' => 'uuid',
            'id' => 'uuid'
        ]);

        $this->deleteResource($this->tenantRolesModel, Arr::get($params, 'id', ''));

        $this->respond(204);

    }

}