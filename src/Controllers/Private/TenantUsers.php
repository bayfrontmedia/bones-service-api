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
use Bayfront\BonesService\Api\Schemas\TenantUserCollection;
use Bayfront\BonesService\Api\Schemas\TenantUserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;

class TenantUsers extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantUsersModel $tenantUsersModel;

    public function __construct(ApiService $apiService, TenantUsersModel $tenantUsersModel)
    {
        parent::__construct($apiService);
        $this->tenantUsersModel = $tenantUsersModel;
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

        $this->validateIsAdmin($this->user);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantUsersModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantUsersModel, $body);

        $this->respond(201, TenantUserResource::create($resource));

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

        try {

            if (!$this->user->inTenant($params['tenant'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->validateQuery($this->getQueryParserRules());

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantUsersModel, $query_filter);

        $this->respond(200, TenantUserCollection::create($collection['list'], $collection['config']));

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

        try {

            if (!$this->user->inTenant($params['tenant'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->tenantUsersModel, $params['id'], [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantUsersModel, $params['id']);

        $this->respond(200, TenantUserResource::create($resource));

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
            'id' => 'required|uuid'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_users:delete'
        ]);

        if ($this->filteredResourceExists($this->tenantUsersModel, $params['id'], [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantUsersModel, $params['id']);
        }

        $this->respond(204);

    }

}