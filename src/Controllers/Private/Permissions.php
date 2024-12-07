<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\PermissionCollection;
use Bayfront\BonesService\Api\Schemas\PermissionResource;
use Bayfront\BonesService\Api\Traits\Auditable;
use Bayfront\BonesService\Api\Traits\UsesOrmModel;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\HttpRequest\Request;

class Permissions extends PrivateApiController implements CrudControllerInterface
{

    use Auditable, UsesOrmModel;

    protected PermissionsModel $permissionsModel;

    /**
     * @param ApiService $apiService
     * @param PermissionsModel $permissionsModel
     * @throws ApiHttpException
     * @throws ApiServiceException
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
    public function create(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validateHeaders(Request::getHeader(), [
            'Content-Type' => 'required|matches:application/json'
        ]);

        /*
         * TODO:
         * getAllowedFieldsWrite does not check for required fields
         */
        $this->validateBodyJson(Request::getBody(), $this->permissionsModel->getAllowedFieldsWrite());

        $resource = $this->createOrmResource($this->permissionsModel, $this->getBody());

        $this->respond(201, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validateQuery(Request::getQuery(), [ // TODO: Make predefined ORM query parser rules
            'filter' => 'isJson',
            'aggregate' => 'isJson'
        ]);

        $collection = $this->listOrmResources($this->permissionsModel, Request::getQuery());

        // Response
        $this->respond(200, PermissionCollection::create($collection['list'], $collection['config']), [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validatePath($params, [
            'id' => 'uuid'
        ]);

        $this->validateQuery(Request::getQuery(), [
            'fields' => 'isString'
        ]);

        $resource = $this->readOrmResource($this->permissionsModel, Arr::get($params, 'id', ''), Request::getQuery());

        $this->respond(200, PermissionResource::create($resource), [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validatePath($params, [
            'id' => 'uuid'
        ]);

        $this->validateHeaders(Request::getHeader(), [
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateBodyJson(Request::getBody(), $this->permissionsModel->getAllowedFieldsWrite());

        $resource = $this->updateOrmResource($this->permissionsModel, Arr::get($params, 'id', ''), $this->getBody());

        $this->respond(200, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validatePath($params, [
            'id' => 'uuid'
        ]);

        $this->deleteOrmResource($this->permissionsModel, Arr::get($params, 'id'));

        $this->respond(204);

    }

}