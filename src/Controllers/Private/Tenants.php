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
use Bayfront\BonesService\Api\Schemas\TenantCollection;
use Bayfront\BonesService\Api\Schemas\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantsModel;

class Tenants extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantsModel $tenantsModel;

    public function __construct(ApiService $apiService, TenantsModel $tenantsModel)
    {
        parent::__construct($apiService);
        $this->tenantsModel = $tenantsModel;
    }

    /**
     * TODO:
     * May need to restrict or slugify "domain"
     *
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function create(array $params): void
    {

        if (!$this->user->isAdmin() && $this->apiService->getConfig('tenant.allow_create') !== true) {
            throw new NotFoundException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantsModel, true);

        if (!$this->user->isAdmin()) {

            if ($body['owner'] != $this->user->getId() || isset($body['enabled'])) {
                throw new BadRequestException('Unable to create resource: Invalid field(s)');
            }

            $body['enabled'] = true;

        }

        $resource = $this->createResource($this->tenantsModel, $body);

        $this->respond(201, TenantResource::create($resource));

    }

    /**
     * TODO:
     * How do users see tenants they belong to?
     * May need to build custom list query.
     *
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function list(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validateQuery($this->getQueryParserRules());

        $collection = $this->listResources($this->tenantsModel);

        $this->respond(200, TenantCollection::create($collection['list'], $collection['config']), [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws UnexpectedException
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'uuid'
        ]);

        if (!$this->user->isAdmin() && !$this->user->inTenant($params['id'])) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->tenantsModel, $params['id']);

        // Response
        $this->respond(200, TenantResource::create($resource), [
            'Cache-Control' => 'max-age=3600'
        ]);

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
            'id' => 'uuid'
        ]);

        $this->validatePermissions($this->user, $params['id'], [
            'tenants:update'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantsModel);

        /*
         * RBAC service will not allow owner to be updated if not already in tenant
         */

        if (!$this->user->isAdmin() && (isset($body['domain']) || isset($body['enabled']))) {
            throw new BadRequestException('Unable to update resource: Invalid field(s)');
        }

        $resource = $this->updateResource($this->tenantsModel, $params['id'], $body);

        $this->respond(200, TenantResource::create($resource));

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
            'id' => 'uuid'
        ]);

        if ($this->apiService->getConfig('tenant.allow_delete') === true) {

            $this->validatePermissions($this->user, $params['id'], [
                'tenants:delete'
            ]);

        } else {
            $this->validateIsAdmin($this->user);
        }

        $this->deleteResource($this->tenantsModel, $params['id']);

        $this->respond(204);

    }

}