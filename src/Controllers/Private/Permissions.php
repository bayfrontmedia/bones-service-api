<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiControllerInterface;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;
use Bayfront\HttpRequest\Request;

class Permissions extends PrivateApiController implements ApiControllerInterface
{

    protected PermissionsModel $permissionsModel;

    public function __construct(ApiService $apiService, PermissionsModel $permissionsModel)
    {
        parent::__construct($apiService);
        $this->permissionsModel = $permissionsModel;
    }

    /**
     * @inheritDoc
     */
    public function create(): void
    {

        try {
            $permission = $this->permissionsModel->create($this->getBody());
        } catch (AlreadyExistsException $e) {
            $this->abort(409, 'Unable to create resource: Existing conflict', $e);
        } catch (DoesNotExistException|InvalidFieldException $e) {
            $this->abort(400, 'Unable to create resource: Invalid or missing field(s)', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to create resource: Unexpected error', $e);
        }

        $this->respond(201, $permission->read());

    }

    /**
     * @inheritDoc
     */
    public function list(): void
    {

        try {
            $permissions = $this->permissionsModel->list(new QueryParser(Request::getQuery()));
        } catch (InvalidRequestException $e) {
            $this->abort(400, 'Unable to list resource: Invalid request', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to list resource: Unexpected error', $e);
        }

        $this->respond(200, $permissions->list());

    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {

        try {
            $permission = $this->permissionsModel->read(Arr::get($params, 'id', ''));
        } catch (DoesNotExistException $e) {
            $this->abort(404, 'Unable to read resource: Resource does not exist', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to read resource: Unexpected error', $e);
        }

        $this->respond(200, $permission);

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {

        try {
            $permission = $this->permissionsModel->update(Arr::get($params, 'id'), $this->getBody());
        } catch (AlreadyExistsException $e) {
            $this->abort(409, 'Unable to update resource: Existing conflict', $e);
        } catch (DoesNotExistException $e) {
            $this->abort(404, 'Unable to update resource: Resource does not exist', $e);
        } catch (InvalidFieldException $e) {
            $this->abort(400, 'Unable to update resource: Invalid or missing field(s)', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to update resource: Unexpected error', $e);
        }

        $this->respond(200, $permission->read());

    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {

        try {
            $this->permissionsModel->delete(Arr::get($params, 'id'));
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to delete resource: Unexpected error', $e);
        }

        $this->respond(204);

    }

}