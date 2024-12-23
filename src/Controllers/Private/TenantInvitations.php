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
use Bayfront\BonesService\Api\Schemas\TenantInvitationCollection;
use Bayfront\BonesService\Api\Schemas\TenantInvitationResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantInvitationsModel;

class TenantInvitations extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantInvitationsModel $tenantInvitationsModel;

    public function __construct(ApiService $apiService, TenantInvitationsModel $tenantInvitationsModel)
    {
        parent::__construct($apiService);
        $this->tenantInvitationsModel = $tenantInvitationsModel;
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
            'tenant_invitations:create',
            'tenant_roles:read'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantInvitationsModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantInvitationsModel, $body);

        $this->respond(201, TenantInvitationResource::create($resource));

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
            'tenant_invitations:read',
            'tenant_roles:read'
        ]);

        $this->validateQuery($this->getQueryParserRules(), true);

        $collection = $this->listResources($this->tenantInvitationsModel);

        $this->respond(200, TenantInvitationCollection::create($collection['list'], $collection['config']));

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
            'tenant_invitations:read',
            'tenant_roles:read'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->tenantInvitationsModel, $params['id']);

        $this->respond(200, TenantInvitationResource::create($resource));

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
            'tenant_invitations:delete'
        ]);

        $this->deleteResource($this->tenantInvitationsModel, $params['id']);

        $this->respond(204);

    }

}