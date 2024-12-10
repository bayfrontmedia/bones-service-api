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
use Bayfront\BonesService\Api\Schemas\UserMetaCollection;
use Bayfront\BonesService\Api\Schemas\UserMetaResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;

class UserMeta extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

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
            'user' => 'uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->userMetaModel, true, [
            'user' => $params['user']
        ]);

        $resource = $this->createResource($this->userMetaModel, $body);

        $this->respond(201, UserMetaResource::create($resource));

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

        $collection = $this->listResources($this->userMetaModel, $query_filter);

        $this->respond(200, UserMetaCollection::create($collection['list'], $collection['config']));

    }

    /**
     * TODO:
     * Update query to list and add filter where user = $params['user']
     *
     * This method will currently return meta for any user.
     *
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'user' => 'uuid',
            'id' => 'uuid'
        ]);

        if (!$this->user->isAdmin() && $this->user->getId() != $params['user']) {
            throw new ForbiddenException();
        }

        $this->validateQuery($this->getFieldParserRules());

        //

        /*
         * TODO:
         * Need to find a better way to see if the resource exists
         * that matches user and id (scoped).
         * This will be used by read, update and delete.
         *
         * Possibly create an API model, but this would force an
         * additional query without making a native ORM function.
         */

        $query_filter = [
            [
                'user' => [
                    'eq' => $params['user']
                ]
            ],
            [
                'id' => [
                    'eq' => $params['id']
                ]
            ]
        ];

        $collection = $this->listResources($this->userMetaModel, $query_filter);

        if (!isset($collection['list'][0])) {
            throw new NotFoundException();
        }

        $this->respond(200, UserMetaResource::create($collection['list'][0]));

        die;

        //

        $resource = $this->readResource($this->userMetaModel, $params['id']);

        $this->respond(200, UserMetaResource::create($resource));

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