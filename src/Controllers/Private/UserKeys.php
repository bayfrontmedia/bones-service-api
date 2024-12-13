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
use Bayfront\BonesService\Api\Schemas\UserKeyCollection;
use Bayfront\BonesService\Api\Schemas\UserKeyResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\UserKeysModel;

class UserKeys extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected UserKeysModel $userKeysModel;

    public function __construct(ApiService $apiService, UserKeysModel $userKeysModel)
    {
        parent::__construct($apiService);
        $this->userKeysModel = $userKeysModel;
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

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->userKeysModel, true, [
            'user' => $params['user']
        ]);

        $resource = $this->createResource($this->userKeysModel, $body);

        $this->respond(201, UserKeyResource::create($resource));

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
            'user' => 'required|uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getQueryParserRules(), true);

        $query_filter = [
            [
                'user' => [
                    'eq' => $params['user']
                ]
            ]
        ];

        $collection = $this->listResources($this->userKeysModel, $query_filter);

        $this->respond(200, UserKeyCollection::create($collection['list'], $collection['config']));

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

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getFieldParserRules());

        if (!$this->filteredResourceExists($this->userKeysModel, $params['id'], [
            [
                'user' => [
                    'eq' => $params['user']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->userKeysModel, $params['id']);

        $this->respond(200, UserKeyResource::create($resource));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function update(array $params): void
    {

        $this->validatePath($params, [
            'user' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        if (!$this->filteredResourceExists($this->userKeysModel, $params['id'], [
            [
                'user' => [
                    'eq' => $params['user']
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

        $body = $this->getResourceBody($this->userKeysModel);

        $resource = $this->updateResource($this->userKeysModel, $params['id'], $body);

        $this->respond(200, UserKeyResource::create($resource));

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

        if ($this->filteredResourceExists($this->userKeysModel, $params['id'], [
            [
                'user' => [
                    'eq' => $params['user']
                ]
            ]
        ])) {
            $this->deleteResource($this->userKeysModel, $params['id']);
        }

        $this->respond(204);

    }

}