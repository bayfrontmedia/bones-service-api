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
use Bayfront\BonesService\Api\Schemas\TenantCollection;
use Bayfront\BonesService\Api\Schemas\TenantInvitationCollection;
use Bayfront\BonesService\Api\Schemas\UserCollection;
use Bayfront\BonesService\Api\Schemas\UserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\TenantInvitationsModel;
use Bayfront\BonesService\Rbac\Models\TenantsModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;

class Users extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected UsersModel $usersModel;

    /**
     * @param ApiService $apiService
     * @param UsersModel $usersModel
     * @throws ApiServiceException
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService, UsersModel $usersModel)
    {
        parent::__construct($apiService);
        $this->usersModel = $usersModel;
    }

    /**
     * Revoke access and refresh keys for current user.
     * Users will still be able to authenticate with an API key,
     * or if access tokens are not revocable.
     *
     * @return void
     * @throws ApiServiceException
     */
    public function logout(): void
    {
        $userMetaModel = new UserMetaModel($this->rbacService);
        $userMetaModel->deleteAllTokens($this->user->getId());
        $this->respond(204);
    }

    /**
     * Read current user.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function me(): void
    {
        $this->read([
            'id' => $this->user->getId()
        ]);
    }

    /**
     * Accept tenant invitation.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function acceptInvitation(): void
    {

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'tenant_id' => 'required|uuid'
        ]);

        $tenantInvitationsModel = new TenantInvitationsModel($this->rbacService);

        try {
            $tenantInvitationsModel->accept($this->user->getEmail(), $body['tenant_id']);
        } catch (DoesNotExistException) {
            throw new NotFoundException();
        } catch (InvalidFieldException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage(), 0, $e);
        }

        $this->respond(204);

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

        $body = $this->getResourceBody($this->usersModel, true);

        $resource = $this->createResource($this->usersModel, $body);

        $this->respond(201, UserResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    public function list(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validateQuery($this->getQueryParserRules());

        $collection = $this->listResources($this->usersModel);

        $this->respond(200, UserCollection::create($collection['list'], $collection['config']));

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

        if (!$this->user->isAdmin() && $params['id'] !== $this->user->getId()) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->usersModel, $params['id']);

        $this->respond(200, UserResource::create($resource));

    }

    /**
     * Non-admin users cannot define admin or enabled fields.
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

        if (!$this->user->isAdmin() && $params['id'] !== $this->user->getId()) {
            throw new ForbiddenException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->usersModel);

        if (!$this->user->isAdmin() &&
            (isset($body['admin']) || isset($body['enabled']))) {
            throw new BadRequestException('Unable to update resource: Invalid field(s)');
        }

        $resource = $this->updateResource($this->usersModel, $params['id'], $body);

        $this->respond(200, UserResource::create($resource));

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

        if ($this->apiService->getConfig('user.allow_delete') === true) {

            if (!$this->user->isAdmin() && $params['id'] !== $this->user->getId()) {
                throw new ForbiddenException();
            }

        } else {
            $this->validateIsAdmin($this->user);
        }

        $this->deleteResource($this->usersModel, $params['id']);

        $this->logout(); // Respond with 204

    }

    /**
     * List user's tenant invitations.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws DoesNotExistException
     * @throws ForbiddenException
     * @throws UnexpectedException
     */
    public function listInvitations(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        if ($params['id'] == $this->user->getId()) {

            $email = $this->user->getEmail();

        } else {

            try {

                $user = $this->usersModel->read($params['id'], [
                    'email'
                ]);

            } catch (InvalidRequestException $e) {
                throw new ApiServiceException($e->getMessage());
            }

            $email = Arr::get($user, 'email', '');

        }

        $this->validateQuery($this->getQueryParserRules());

        $query_filter = [
            [
                'email' => [
                    'eq' => $email
                ]
            ]
        ];

        $tenantInvitationsModel = new TenantInvitationsModel($this->rbacService);

        $collection = $this->listResources($tenantInvitationsModel, $query_filter);

        $this->respond(200, TenantInvitationCollection::create($collection['list'], $collection['config']));

    }

    /**
     * List tenants user belongs to.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws DoesNotExistException
     * @throws ForbiddenException
     * @throws UnexpectedException
     */
    public function listTenants(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getQueryParserRules());

        // Get array of tenant ID's

        $tenantUsersModel = new TenantUsersModel($this->rbacService);

        try {

            $tenantsCollection = $tenantUsersModel->list(new QueryParser([
                'fields' => 'tenant',
                'filter' => [
                    [
                        'user' => [
                            'eq' => $params['id']
                        ]
                    ]
                ]
            ]));

        } catch (InvalidRequestException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $tenant_ids = Arr::pluck($tenantsCollection->list(), 'tenant');

        // Check user exists if no tenant ID's found

        if (empty($tenant_ids)) {

            if (!$this->usersModel->exists($params['id'])) {
                throw new DoesNotExistException();
            }

        }

        // List tenants

        $tenantsModel = new TenantsModel($this->rbacService);

        $query_filter = [
            [
                'id' => [
                    'in' => implode(',', $tenant_ids)
                ]
            ]
        ];

        $collection = $this->listResources($tenantsModel, $query_filter);

        $this->respond(200, TenantCollection::create($collection['list'], $collection['config']));

    }

}