<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiControllerInterface;
use Bayfront\BonesService\Api\Traits\Auditable;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\InvalidFieldException;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\PermissionsModel;

class Permissions extends PrivateApiController implements ApiControllerInterface
{

    use Auditable;

    protected PermissionsModel $permissionsModel;

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
    public function create(): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to create resource: Insufficient permissions');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Function
        try {
            $permission = $this->permissionsModel->create($this->getBody());
        } catch (AlreadyExistsException $e) {
            $this->abort(409, 'Unable to create resource: Existing conflict', $e);
        } catch (DoesNotExistException|InvalidFieldException $e) {
            $this->abort(400, 'Unable to create resource: Invalid or missing field(s)', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to create resource: Unexpected error', $e);
        }

        // Schema
        $schema = [
            'data' => $permission->read()
        ];

        // Response
        $this->respond(201, $schema);

    }

    /**
     * @inheritDoc
     */
    public function list(): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to create resource: Insufficient permissions');
        }

        // Require headers

        // Function
        try {
            $collection = $this->permissionsModel->list(new QueryParser($this->getQuery()));
        } catch (InvalidRequestException $e) {
            $this->abort(400, 'Unable to list resource: Invalid request', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to list resource: Unexpected error', $e);
        }

        try {
            $pagination = $collection->getPagination();
            $aggregate = $collection->getAggregate();
        } catch (InvalidRequestException $e) {
            $this->abort(400, 'Unable to list resource: Invalid request', $e);
        }

        if (empty($pagination) && empty($aggregate)) {

            // Schema
            $schema = $collection->list();

        } else {

            // Schema
            $schema = [
                'data' => $collection->list()
            ];

            if (!empty($pagination)) {
                $schema['pagination'] = $pagination;
            }

            if (!empty($aggregate)) {
                $schema['aggregate'] = $aggregate;
            }

        }

        // Response
        $this->respond(200, $schema, [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to read resource: Insufficient permissions');
        }

        // Require headers

        // Function
        try {
            $resource = $this->permissionsModel->read(Arr::get($params, 'id', ''));
        } catch (DoesNotExistException $e) {
            $this->abort(404, 'Unable to read resource: Resource does not exist', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to read resource: Unexpected error', $e);
        }

        // Schema
        $schema = [
            'data' => $resource
        ];

        // Response
        $this->respond(200, $schema, [
            'Cache-Control' => 'max-age=3600'
        ]);

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to update resource: Insufficient permissions');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        // Function
        try {
            $resource = $this->permissionsModel->update(Arr::get($params, 'id'), $this->getBody());
        } catch (AlreadyExistsException $e) {
            $this->abort(409, 'Unable to update resource: Existing conflict', $e);
        } catch (DoesNotExistException $e) {
            $this->abort(404, 'Unable to update resource: Resource does not exist', $e);
        } catch (InvalidFieldException $e) {
            $this->abort(400, 'Unable to update resource: Invalid or missing field(s)', $e);
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to update resource: Unexpected error', $e);
        }

        // Schema
        $schema = [
            'data' => $resource->read()
        ];

        // Response
        $this->respond(200, $schema);

    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {

        // Check permissions
        if (!$this->user->isAdmin()) {
            $this->abort(403, 'Unable to delete resource: Insufficient permissions');
        }

        // Require headers

        // Function
        try {
            $this->permissionsModel->delete(Arr::get($params, 'id'));
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to delete resource: Unexpected error', $e);
        }

        // Response
        $this->respond(204);

    }

}