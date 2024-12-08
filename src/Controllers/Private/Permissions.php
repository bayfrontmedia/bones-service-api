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
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;

class Permissions extends PrivateApiController implements CrudControllerInterface
{

    use Auditable, UsesResourceModel;

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->permissionsModel, true);

        die('good');
        $resource = $this->createResource($this->permissionsModel, $body);

        $this->respond(201, PermissionResource::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {

        $this->validateIsAdmin($this->user);

        $this->validateQuery($this->getQueryParserRules());

        $collection = $this->listResources($this->permissionsModel);

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

        $this->validateQuery($this->getFieldParserRules());

        $resource = $this->readResource($this->permissionsModel, Arr::get($params, 'id', ''));

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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->permissionsModel);

        $resource = $this->updateResource($this->permissionsModel, Arr::get($params, 'id', ''), $body);

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

        $this->deleteResource($this->permissionsModel, Arr::get($params, 'id'));

        $this->respond(204);

    }

}