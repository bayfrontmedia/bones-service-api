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
use Bayfront\BonesService\Api\Schemas\TenantPermissionCollection;
use Bayfront\BonesService\Api\Schemas\TenantPermissionResouce;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Models\TenantPermissionsModel;

class TenantPermissions extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantPermissionsModel $tenantPermissionsModel;

    public function __construct(ApiService $apiService, TenantPermissionsModel $tenantPermissionsModel)
    {
        parent::__construct($apiService);
        $this->tenantPermissionsModel = $tenantPermissionsModel;
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
            'tenant' => 'required|uuid'
        ]);

        $this->validateIsAdmin($this->user);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getResourceBody($this->tenantPermissionsModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantPermissionsModel, $body);

        $this->respond(201, TenantPermissionResouce::create($resource));

    }

    /**
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     */
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules(), true);

        /** @noinspection DuplicatedCode */
        try {

            if ($this->user->canDoAll($params['tenant'], [
                'tenant_permissions:read'
            ])) {

                $query_filter = [
                    [
                        'tenant' => [
                            'eq' => $params['tenant']
                        ]
                    ]
                ];

            } else {

                $query_filter = [
                    [
                        'tenant' => [
                            'eq' => $params['tenant']
                        ]
                    ],
                    [
                        'permission' => [
                            'in' => implode(',', Arr::pluck($this->user->getPermissions($params['tenant']), 'id'))
                        ]
                    ]
                ];

            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $collection = $this->listResources($this->tenantPermissionsModel, $query_filter);

        $this->respond(200, TenantPermissionCollection::create($collection['list'], $collection['config']));

    }

    /**
     * @inheritDoc
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function read(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        /** @noinspection DuplicatedCode */
        try {

            if ($this->user->canDoAll($params['tenant'], [
                'tenant_permissions:read'
            ])) {

                $query_filter = [
                    [
                        'tenant' => [
                            'eq' => $params['tenant']
                        ]
                    ]
                ];

            } else {

                $query_filter = [
                    [
                        'tenant' => [
                            'eq' => $params['tenant']
                        ]
                    ],
                    [
                        'permission' => [
                            'in' => implode(',', Arr::pluck($this->user->getPermissions($params['tenant']), 'id'))
                        ]
                    ]
                ];

            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        if (!$this->filteredResourceExists($this->tenantPermissionsModel, $params['id'], $query_filter)) {
            throw new NotFoundException();
        }

        $resource = $this->readResource($this->tenantPermissionsModel, $params['id']);

        $this->respond(200, TenantPermissionResouce::create($resource));

    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {
        // Non-routed (relationship)
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
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateIsAdmin($this->user);

        if ($this->filteredResourceExists($this->tenantPermissionsModel, $params['id'], [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ]
        ])) {
            $this->deleteResource($this->tenantPermissionsModel, $params['id']);
        }

        $this->respond(204);

    }

}