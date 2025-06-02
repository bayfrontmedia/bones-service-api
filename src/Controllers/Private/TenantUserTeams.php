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
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\TenantUserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantTeamsModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;
use Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel;

class TenantUserTeams extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, TenantUserResource, UsesResourceModel;

    protected TenantUserTeamsModel $tenantUserTeamsModel;
    protected TenantUsersModel $tenantUsersModel;

    public function __construct(ApiService $apiService, TenantUserTeamsModel $tenantUserTeamsModel, TenantUsersModel $tenantUsersModel)
    {
        parent::__construct($apiService);
        $this->tenantUserTeamsModel = $tenantUserTeamsModel;
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
            'tenant_user_teams:update'
        ]);

        $body = $this->getResourceBody($this->tenantUserTeamsModel, true, [
            'tenant_user' => $params['tenant_user']
        ]);

        if (isset($body['team']) && !$this->tenantResourceExists(new TenantTeamsModel($this->rbacService), $params['tenant'], $body['team'])) {
            throw new BadRequestException('Invalid team');
        }

        $resource = $this->createResource($this->tenantUserTeamsModel, $body);

        $this->respond(201, TenantUserTeamResource::create($resource));

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
                    'tenant_teams:read'
                ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $collection = $this->listTenantUserResources($this->tenantUserTeamsModel, $params['tenant_user']);

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

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantUserExists($this->tenantUsersModel, $params['tenant'], $params['tenant_user']);

        $this->validateTenantUserResourceExists($this->tenantUserTeamsModel, $params['tenant_user'], $params['id']);

        try {

            if (!$this->user->canDoAll($params['tenant'], [
                    'tenant_teams:read'
                ]) && $this->user->getTenantUserId($params['tenant']) != $params['tenant_user']) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
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
            'tenant_user_teams:update'
        ]);

        if ($this->tenantUserResourceExists($this->tenantUserTeamsModel, $params['tenant_user'], $params['id'])) {
            $this->deleteResource($this->tenantUserTeamsModel, $params['id']);
        }

        $this->respond(204);

    }

}