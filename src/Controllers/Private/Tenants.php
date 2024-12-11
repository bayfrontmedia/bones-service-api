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
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\TenantCollection;
use Bayfront\BonesService\Api\Schemas\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantsModel;
use Bayfront\StringHelpers\Str;

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
     * Non-admin users cannot define owner, domain or enabled values.
     * Domain is always transformed to a lowercase URL-friendly slug.
     *
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws ForbiddenException
     */
    public function create(array $params): void
    {

        if (!$this->user->isAdmin() && $this->apiService->getConfig('tenant.allow_create') !== true) {
            throw new ForbiddenException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if ($this->user->isAdmin()) {

            $body = $this->getResourceBody($this->tenantsModel, true);

        } else {

            $body = $this->getResourceBody($this->tenantsModel, true, [
                'owner' => $this->user->getId(),
                'domain' => Str::random(16, Str::RANDOM_TYPE_ALPHANUMERIC), // Temp placeholder
                'enabled' => $this->apiService->getConfig('tenant.auto_enabled', true)
            ]);

            $body['domain'] = $body['name'];

        }

        $resource = $this->createResource($this->tenantsModel, $body);

        $this->respond(201, TenantResource::create($resource));

    }

    /**
     * Non-admin users can only return tenants they belong to.
     *
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws UnexpectedException
     */
    public function list(array $params): void
    {

        /*
         * No permission restrictions.
         * Users can always read tenants they belong to.
         */

        $this->validateQuery($this->getQueryParserRules());

        $query_filter = [];

        if (!$this->user->isAdmin()) { // Restrict non-admins to only return tenants they belong to

            $query_filter = [
                [
                    'id' => [
                        'in' => implode(',', Arr::pluck($this->user->getTenants(), 'id'))
                    ]
                ]
            ];

        }

        $collection = $this->listResources($this->tenantsModel, $query_filter);

        $this->respond(200, TenantCollection::create($collection['list'], $collection['config']));

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
            'id' => 'required|uuid'
        ]);

        if (!$this->user->isAdmin() && !$this->user->inTenant($params['id'])) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->tenantsModel, $params['id']);

        $this->respond(200, TenantResource::create($resource));

    }

    /**
     * Non-admin users cannot update domain or enabled values.
     * Domain is always transformed to a lowercase URL-friendly slug.
     *
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

        $this->validateHasPermissions($this->user, $params['id'], [
            'tenant:update'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        /*
         * RBAC service will not allow owner to be updated if not already in tenant
         */

        $body = $this->getResourceBody($this->tenantsModel);

        if (!$this->user->isAdmin() &&
            (isset($body['domain']) || isset($body['enabled']))) {
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
     * @throws UnexpectedException
     */
    public function delete(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        if ($this->apiService->getConfig('tenant.allow_delete') === true) {

            if (!$this->user->isAdmin() && !$this->user->ownsTenant($params['id'])) {
                throw new ForbiddenException();
            }

        } else {
            $this->validateIsAdmin($this->user);
        }

        $this->deleteResource($this->tenantsModel, $params['id']);

        $this->respond(204);

    }

}