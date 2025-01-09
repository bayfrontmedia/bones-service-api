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
use Bayfront\BonesService\Api\Schemas\UserMetaCollection;
use Bayfront\BonesService\Api\Schemas\UserMetaResource;
use Bayfront\BonesService\Api\Traits\UserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;

class UserMeta extends PrivateApiController implements CrudControllerInterface
{

    use UserResource, UsesResourceModel;

    protected UserMetaModel $userMetaModel;

    public function __construct(ApiService $apiService, UserMetaModel $userMetaModel)
    {
        parent::__construct($apiService);
        $this->userMetaModel = $userMetaModel;
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
            'user' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $body = $this->getResourceBody($this->userMetaModel, true, [
            'user' => $params['user']
        ]);

        $resource = $this->createResource($this->userMetaModel, $body);

        $this->respond(201, UserMetaResource::create($resource));

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

        /** @noinspection DuplicatedCode */
        $this->validatePath($params, [
            'user' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateUserExists(new UsersModel($this->rbacService), $params['user']);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $query_filter = [
            [
                'user' => [
                    'eq' => $params['user']
                ]
            ]
        ];

        $collection = $this->listResources($this->userMetaModel, $query_filter);

        $this->respond(200, UserMetaCollection::create($collection['list'], $collection['config']));

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
            'user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $this->validateUserResourceExists($this->userMetaModel, $params['user'], $params['id']);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $resource = $this->readResource($this->userMetaModel, $params['id']);

        $this->respond(200, UserMetaResource::create($resource));

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
            'user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateUserResourceExists($this->userMetaModel, $params['user'], $params['id']);


        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $body = $this->getResourceBody($this->userMetaModel, false, [], [
            'user'
        ]);

        $resource = $this->updateResource($this->userMetaModel, $params['id'], $body);

        $this->respond(200, UserMetaResource::create($resource));

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
            'user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        if ($this->userResourceExists($this->userMetaModel, $params['user'], $params['id'])) {
            $this->deleteResource($this->userMetaModel, $params['id']);
        }

        $this->respond(204);

    }

}