<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\UserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;

class Users extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;
    protected UsersModel $usersModel;

    /**
     * @param ApiService $apiService
     * @param UsersModel $usersModel
     * @throws ApiHttpException
     * @throws ApiServiceException
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
     * @throws ApiHttpException
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
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function me(): void
    {
        $this->read([
            'id' => $this->user->getId()
        ]);
    }

    /**
     * @inheritDoc
     */
    public function create(array $params): void
    {
        // TODO: Implement create() method.

    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {

    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {

        if (!$this->user->isAdmin() && Arr::get($params, 'id', '') !== $this->user->getId()) {
            $this->abort(403);
        }

        $this->validatePath($params, [
            'id' => 'uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->usersModel, Arr::get($params, 'id', ''));

        // Response
        $this->respond(200, UserResource::create($resource), [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {
        // TODO: Implement delete() method.
    }
}