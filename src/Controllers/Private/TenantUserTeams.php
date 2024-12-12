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
use Bayfront\BonesService\Api\Schemas\TenantUserTeamCollection;
use Bayfront\BonesService\Api\Schemas\TenantUserTeamResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel;

class TenantUserTeams extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantUserTeamsModel $tenantUserTeamsModel;

    public function __construct(ApiService $apiService, TenantUserTeamsModel $tenantUserTeamsModel)
    {
        parent::__construct($apiService);
        $this->tenantUserTeamsModel = $tenantUserTeamsModel;
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
            'tenant_users.update',
            'tenant_teams:update'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantUserTeamsModel, true, [
            'tenant_user' => $params['tenant_user']
        ]);

        $resource = $this->createResource($this->tenantUserTeamsModel, $body);

        $this->respond(201, TenantUserTeamResource::create($resource));

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

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_teams:read'
                ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->validateQuery($this->getQueryParserRules());

        $query_filter = [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ];

        $collection = $this->listResources($this->tenantUserTeamsModel, $query_filter);

        $this->respond(200, TenantUserTeamCollection::create($collection['list'], $collection['config']));

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

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_teams:read'
                ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->tenantUserTeamsModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantUserTeamsModel, $params['id']);

        $this->respond(200, TenantUserTeamResource::create($resource));

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
            'tenant_users:update',
            'tenant_teams:update'
        ]);

        if ($this->filteredResourceExists($this->tenantUserTeamsModel, $params['id'], [
            [
                'tenant_user' => [
                    'eq' => $params['tenant_user']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantUserTeamsModel, $params['id']);
        }

        $this->respond(204);

    }

}