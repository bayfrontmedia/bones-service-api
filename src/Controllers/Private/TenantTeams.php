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
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Schemas\TenantTeamCollection;
use Bayfront\BonesService\Api\Schemas\TenantTeamResource;
use Bayfront\BonesService\Api\Schemas\TenantUserCollection;
use Bayfront\BonesService\Api\Traits\TenantResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Orm\Exceptions\InvalidRequestException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Utilities\Parsers\QueryParser;
use Bayfront\BonesService\Rbac\Models\TenantTeamsModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;
use Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel;

class TenantTeams extends PrivateApiController implements CrudControllerInterface
{

    use TenantResource, UsesResourceModel;

    protected TenantTeamsModel $tenantTeamsModel;

    /**
     * @param ApiService $apiService
     * @param TenantTeamsModel $tenantTeamsModel
     * @throws ApiServiceException
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService, TenantTeamsModel $tenantTeamsModel)
    {
        parent::__construct($apiService);
        $this->tenantTeamsModel = $tenantTeamsModel;
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

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_teams:create'
        ]);

        $body = $this->getResourceBody($this->tenantTeamsModel, true, [
            'tenant' => $params['tenant']
        ]);

        $resource = $this->createResource($this->tenantTeamsModel, $body);

        $this->respond(201, TenantTeamResource::create($resource));

    }

    /**
     * @inheritDoc
     * @param array $params
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function list(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        $this->validateTenantExists($params['tenant']);

        try {

            if ($this->user->canDoAll($params['tenant'], [
                'tenant_teams:read'
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
                        'id' => [
                            'in' => implode(',', Arr::pluck($this->user->getTeams($params['tenant']), 'id'))
                        ]
                    ]
                ];

            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $collection = $this->listResources($this->tenantTeamsModel, $query_filter);

        $this->respond(200, TenantTeamCollection::create($collection['list'], $collection['config']));

    }

    /**
     * Validate user can read team.
     *
     * @param string $tenant
     * @param string $team_id
     * @return void
     * @throws ApiServiceException
     * @throws ForbiddenException
     */
    private function validateCanRead(string $tenant, string $team_id): void
    {

        try {

            if (!$this->user->canDoAll($tenant, [
                    'tenant_teams:read'
                ]) && !$this->user->inTeam($tenant, $team_id)) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

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
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getFieldParserRules());

        $this->validateTenantResourceExists($this->tenantTeamsModel, $params['tenant'], $params['id']);

        $this->validateCanRead($params['tenant'], $params['id']);

        $resource = $this->readResource($this->tenantTeamsModel, $params['id']);

        $this->respond(200, TenantTeamResource::create($resource));

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
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $this->validateTenantResourceExists($this->tenantTeamsModel, $params['tenant'], $params['id']);

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_teams:update'
        ]);

        $body = $this->getResourceBody($this->tenantTeamsModel, false, [], [
            'tenant'
        ]);

        $resource = $this->updateResource($this->tenantTeamsModel, $params['id'], $body);

        $this->respond(200, TenantTeamResource::create($resource));

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

        $this->validateHasPermissions($this->user, $params['tenant'], [
            'tenant_teams:delete'
        ]);

        if ($this->tenantResourceExists($this->tenantTeamsModel, $params['tenant'], $params['id'])) {
            $this->deleteResource($this->tenantTeamsModel, $params['id']);
        }

        $this->respond(204);

    }

    /**
     * List tenant users who belong to team.
     *
     * @param array $params
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ForbiddenException
     * @throws NotFoundException
     */
    public function listUsers(array $params): void
    {

        $this->validatePath($params, [
            'tenant' => 'required|uuid',
            'id' => 'required|uuid'
        ]);

        $this->validateQuery($this->getQueryParserRules());

        // Ensure tenant team exists

        $this->validateTenantResourceExists($this->tenantTeamsModel, $params['tenant'], $params['id']);

        $this->validateCanRead($params['tenant'], $params['id']);

        // Get array of tenant user ID's

        $tenantUserTeamsModel = new TenantUserTeamsModel($this->rbacService);

        try {

            $teamsCollection = $tenantUserTeamsModel->list(new QueryParser([
                'fields' => 'tenant_user',
                'filter' => [
                    [
                        'team' => [
                            'eq' => $params['id']
                        ]
                    ]
                ]
            ]), true);

        } catch (InvalidRequestException|UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        /** @noinspection DuplicatedCode */
        $user_ids = Arr::pluck($teamsCollection->list(), 'tenant_user');

        // List users

        $tenantUsersModel = new TenantUsersModel($this->rbacService);

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $params['tenant']
                ]
            ],
            [
                'id' => [
                    'in' => implode(',', $user_ids)
                ]
            ]
        ];

        $collection = $this->listResources($tenantUsersModel, $query_filter);

        $this->respond(200, TenantUserCollection::create($collection['list'], $collection['config']));

    }

}