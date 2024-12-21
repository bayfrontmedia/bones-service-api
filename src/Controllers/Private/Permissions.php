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
use Bayfront\BonesService\Api\Schemas\PermissionCollection;
use Bayfront\BonesService\Api\Schemas\PermissionResource;
use Bayfront\BonesService\Api\Schemas\TenantRoleCollection;
use Bayfront\BonesService\Api\Traits\Auditable;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolePermissionsModel;
use Bayfront\BonesService\Rbac\Models\TenantRolesModel;

class Permissions extends PrivateApiController implements CrudControllerInterface
{

    use Auditable, UsesResourceModel;

    protected PermissionsModel $permissionsModel;

    /**
     * @param ApiService $apiService
     * @param PermissionsModel $permissionsModel
     * @throws ApiServiceException
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService, PermissionsModel $permissionsModel)
    {
        parent::__construct($apiService);
        $this->permissionsModel = $permissionsModel;
    }

    /**
     * @inheritDoc
     */
    public function getAuditableFunctions(): array
    {
        return [
            'create',
            'list',
            'read',
            'update',
            'delete'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAuditableActions(): array
    {
        return [
            self::AUDIT_ACTION_CREATED,
            self::AUDIT_ACTION_UPDATED,
            self::AUDIT_ACTION_TRASHED,
            self::AUDIT_ACTION_RESTORED,
            self::AUDIT_ACTION_DELETED
        ];
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

        $this->validateIsAdmin($this->user);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->permissionsModel, true);

        $resource = $this->createResource($this->permissionsModel, $body);

        $this->respond(201, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     */
    public function list(array $params): void
    {

        $this->validateQuery($this->getQueryParserRules(), true);

        $collection = $this->listResources($this->permissionsModel);

        $this->respond(200, PermissionCollection::create($collection['list'], $collection['config']));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->permissionsModel, $params['id']);

        $this->respond(200, PermissionResource::create($resource));

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
            'id' => 'required|uuid'
        ]);

        $this->validateIsAdmin($this->user);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->permissionsModel);

        $resource = $this->updateResource($this->permissionsModel, $params['id'], $body);

        $this->respond(200, PermissionResource::create($resource));

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

        $this->validateIsAdmin($this->user);

        $this->deleteResource($this->permissionsModel, $params['id']);

        $this->respond(204);

    }

    /**
     * List roles with permission.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws DoesNotExistException
     * @throws ForbiddenException
     * @throws UnexpectedException
     */
    public function listRoles(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        $this->validateIsAdmin($this->user);

        $this->validateQuery($this->getQueryParserRules(), true);

        // Get array of role ID's

        $tenantRolePermissionsModel = new TenantRolePermissionsModel($this->rbacService);

        try {

            $rolePermissionsCollection = $tenantRolePermissionsModel->list(new QueryParser([
                'fields' => 'role',
                'filter' => [
                    [
                        'permission' => [
                            'eq' => $params['id']
                        ]
                    ]
                ]
            ]), true);

        } catch (InvalidRequestException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $role_ids = Arr::pluck($rolePermissionsCollection->list(), 'role');

        // Check permission exists if no role ID's found

        if (empty($role_ids)) {

            if (!$this->permissionsModel->exists($params['id'])) {
                throw new DoesNotExistException();
            }

        }

        // List roles

        $tenantRolesModel = new TenantRolesModel($this->rbacService);

        $query_filter = [
            [
                'id' => [
                    'in' => implode(',', $role_ids)
                ]
            ]
        ];

        $collection = $this->listResources($tenantRolesModel, $query_filter);

        $this->respond(200, TenantRoleCollection::create($collection['list'], $collection['config']));

    }

}