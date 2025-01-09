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
use Bayfront\Validator\Validator;

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
     * Users will still be able to authenticate with an API key
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
     * Validate user meta.
     *
     * @param array $body
     * @param string $action (create/update)
     * @return void
     * @throws BadRequestException
     */
    private function validateUserMeta(array $body, string $action): void
    {

        $meta_rules = $this->apiService->getConfig('meta.user', []);

        /** @noinspection DuplicatedCode */
        if (isset($body['meta']) && is_array($body['meta']) && !empty($meta_rules)) {

            $validator = new Validator();

            $validator->validate(Arr::dot($body['meta']), $meta_rules, false, true);

            if (!empty(Arr::except(Arr::dot($body['meta']), array_keys($meta_rules))) || !$validator->isValid()) {
                throw new BadRequestException('Unable to ' . $action . ' resource: Invalid meta field(s)');
            }

        }

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateIsAdmin($this->user);

        $body = $this->getResourceBody($this->usersModel, true);

        $this->validateUserMeta($body, 'create');

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

        $this->validateQuery($this->getQueryParserRules());

        $this->validateIsAdmin($this->user);

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

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->user->isAdmin() && $params['id'] !== $this->user->getId()) {
            throw new ForbiddenException();
        }

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if (!$this->user->isAdmin() && $params['id'] !== $this->user->getId()) {
            throw new ForbiddenException();
        }

        $body = $this->getResourceBody($this->usersModel);

        if (!$this->user->isAdmin() &&
            (isset($body['admin']) || isset($body['enabled']))) {
            throw new BadRequestException('Unable to update resource: Invalid field(s)');
        }

        $this->validateUserMeta($body, 'update');

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

        if ($params['id'] == $this->user->getId()) {
            $this->logout(); // Respond with 204
        } else {
            $this->respond(204);
        }

    }

    /**
     * Get email from user ID, checking current user before querying.
     *
     * @param string $user_id
     * @return string
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    private function getEmail(string $user_id): string
    {

        if ($user_id == $this->user->getId()) {

            return $this->user->getEmail();

        } else {

            try {

                // Checks user exists

                $user = $this->usersModel->read($user_id, [
                    'email'
                ]);

            } catch (DoesNotExistException) {
                throw new NotFoundException();
            } catch (InvalidRequestException|UnexpectedException $e) {
                throw new ApiServiceException($e->getMessage());
            }

            return Arr::get($user, 'email', '');

        }

    }

    /**
     * List user's tenant invitations.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listInvitations(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        // Ensures user exists

        $email = $this->getEmail($params['id']);

        // List invitations

        $tenantInvitationsModel = new TenantInvitationsModel($this->rbacService);

        $query_filter = [
            [
                'email' => [
                    'eq' => $email
                ]
            ]
        ];

        $collection = $this->listResources($tenantInvitationsModel, $query_filter);

        $this->respond(200, TenantInvitationCollection::create($collection['list'], $collection['config']));

    }

    /**
     * Accept tenant invitation.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function acceptInvitation(array $params): void
    {

        $this->validatePath($params, [
            'user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $tenantInvitationsModel = new TenantInvitationsModel($this->rbacService);

        try {
            $tenantInvitationsModel->acceptFromId($params['id']);
        } catch (DoesNotExistException) {
            throw new NotFoundException();
        } catch (InvalidFieldException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage(), 0, $e);
        }

        $this->respond(204);

    }

    /**
     * List tenants user belongs to.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listTenants(array $params): void
    {

        $this->validatePath($params, [
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        // Ensure user exists

        try {
            if (!$this->usersModel->exists($params['id'])) {
                throw new NotFoundException();
            }
        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

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
            ]), true);

        } catch (InvalidRequestException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $tenant_ids = Arr::pluck($tenantsCollection->list(), 'tenant');

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