<?php

namespace Bayfront\BonesService\Api\Controllers\Private;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Controllers\Abstracts\PrivateApiController;
use Bayfront\BonesService\Api\Interfaces\CrudControllerInterface;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantUserTeamsModel;

class TenantUserTeams extends PrivateApiController implements CrudControllerInterface
{

    use UsesResourceModel;

    protected TenantUserTeamsModel $tenantUserTeamsModel;

    public function __construct(ApiService $apiService, TenantUserTeamsModel $tenantUserTeamsModel)
    {
        parent::__construct($apiService);
        $this->tenantUserTeamsModel = $tenantUserTeamsModel;
    }

    /**
     * @inheritDoc
     */
    public function create(array $params): void
    {
        // TODO: Implement create() method.
    }

    /**
     * @inheritDoc
     */
    public function list(array $params): void
    {
        // TODO: Implement list() method.
    }

    /**
     * @inheritDoc
     */
    public function read(array $params): void
    {
        // TODO: Implement read() method.
    }

    /**
     * @inheritDoc
     */
    public function update(array $params): void
    {
        // TODO: Implement update() method.
    }

    /**
     * @inheritDoc
     */
    public function delete(array $params): void
    {
        // TODO: Implement delete() method.
    }
}