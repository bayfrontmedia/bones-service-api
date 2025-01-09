<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Models\ResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantsModel;

trait TenantResource
{

    use UsesResourceModel;

    /**
     * Validate tenant exists.
     *
     * @param string $tenant_id
     * @return void
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    protected function validateTenantExists(string $tenant_id): void
    {

        $tenantsModel = new TenantsModel($this->rbacService);

        try {

            if (!$tenantsModel->exists($tenant_id)) {
                throw new NotFoundException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

    }

    /**
     * Does tenant scoped resource exist?
     *
     * @param ResourceModel $resourceModel
     * @param string $tenant_id (With database column of "tenant")
     * @param string $resource_id
     * @return bool
     * @throws ApiServiceException
     */
    protected function tenantResourceExists(ResourceModel $resourceModel, string $tenant_id, string $resource_id): bool
    {

        return $this->resourceExists($resourceModel, $resource_id, [
            [
                'tenant' => [
                    'eq' => $tenant_id
                ]
            ]
        ]);

    }

    /**
     * Validate tenant scoped resource exists.
     *
     * @param ResourceModel $resourceModel
     * @param string $tenant_id (With database column of "tenant")
     * @param string $resource_id
     * @return void
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    protected function validateTenantResourceExists(ResourceModel $resourceModel, string $tenant_id, string $resource_id): void
    {

        if (!$this->tenantResourceExists($resourceModel, $tenant_id, $resource_id)) {
            throw new NotFoundException();
        }

    }

    /**
     * List tenant resources.
     *
     * @param ResourceModel $resourceModel
     * @param string $tenant_id (With database column of "tenant")
     * @return array (Array returned from listResources method)
     * @throws ApiServiceException
     * @throws BadRequestException
     */
    protected function listTenantResources(ResourceModel $resourceModel, string $tenant_id): array
    {

        $query_filter = [
            [
                'tenant' => [
                    'eq' => $tenant_id
                ]
            ]
        ];

        return $this->listResources($resourceModel, $query_filter);

    }

}