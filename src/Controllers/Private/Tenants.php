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
use Bayfront\Validator\Validator;

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
     * Validate tenant meta.
     *
     * @param array $body
     * @param string $action (create/update)
     * @return void
     * @throws BadRequestException
     */
    private function validateTenantMeta(array $body, string $action): void
    {

        $meta_rules = $this->apiService->getConfig('meta.tenant', []);

        /** @noinspection DuplicatedCode */
        if (!empty($meta_rules)) {

            $meta = (array)Arr::get($body, 'meta', []);

            if ($action == 'update') {

                foreach ($meta_rules as $k => $v) {

                    if (array_key_exists($k, $meta)) {

                        if (str_contains($v, 'required') && $meta[$k] === null) {
                            throw new BadRequestException('Unable to ' . $action . ' resource: Missing required meta field(s)');
                        }

                    }

                    $meta_rules[$k] = str_replace([
                        'required|',
                        '|required',
                        'required'
                    ], '', $v);

                }

            }

            $validator = new Validator();

            $validator->validate($meta, $meta_rules, false, true);

            if (!empty(Arr::except($meta, array_keys($meta_rules))) || !$validator->isValid()) {
                throw new BadRequestException('Unable to ' . $action . ' resource: Invalid and/or missing meta field(s)');
            }

        }

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if (!$this->user->isAdmin() && $this->apiService->getConfig('tenant.allow_create') !== true) {
            throw new ForbiddenException();
        }

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

        $this->validateTenantMeta($body, 'create');

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

            try {

                $query_filter = [
                    [
                        'id' => [
                            'in' => implode(',', Arr::pluck($this->user->getTenants(), 'id'))
                        ]
                    ]
                ];

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

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
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        try {

            if (!$this->user->isAdmin() && !$this->user->inTenant($params['id'])) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $resource = $this->readResource($this->tenantsModel, $params['id']);

        $this->respond(200, TenantResource::create($resource));

    }

    /**
     * Non-admin users cannot update domain or enabled values.
     * Domain is always transformed to a lowercase URL-friendly slug.
     * Owner cannot be set to user not already in tenant.
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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateHasPermissions($this->user, $params['id'], [
            'tenant:update'
        ]);

        /*
         * RBAC service will not allow owner to be updated if not already in tenant
         */

        $body = $this->getResourceBody($this->tenantsModel);

        if (!$this->user->isAdmin() &&
            (isset($body['domain']) || isset($body['enabled']))) {
            throw new BadRequestException('Unable to update resource: Invalid field(s)');
        }

        $this->validateTenantMeta($body, 'update');

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
            'id' => 'required|uuid'
        ]);

        if ($this->apiService->getConfig('tenant.allow_delete') === true) {

            try {

                if (!$this->user->isAdmin() && !$this->user->ownsTenant($params['id'])) {
                    throw new ForbiddenException();
                }

            } catch (UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else {
            $this->validateIsAdmin($this->user);
        }

        $this->deleteResource($this->tenantsModel, $params['id']);

        $this->respond(204);

    }

}