<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
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
            'user' => 'uuid'
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
            'user' => 'uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getQueryParserRules());

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
     */
    public function read(array $params): void
    {
        // TODO: Implement read() method.
        $this->respond(200, ['todo']);
    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     */
    public function update(array $params): void
    {
        // TODO: Implement update() method.
        $this->respond(200, ['todo']);
    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     */
    public function delete(array $params): void
    {
        // TODO: Implement delete() method.
        $this->respond(200, ['todo']);
    }
}