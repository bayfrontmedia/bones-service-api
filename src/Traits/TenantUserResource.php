<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Orm\Models\ResourceModel;
use Bayfront\BonesService\Rbac\Models\TenantUsersModel;

trait TenantUserResource
{

    use UsesResourceModel;

    /**
     * Validate tenant user exists in tenant.
     *
     * @param TenantUsersModel $tenantUsersModel
     * @param string $tenant_id (With database column of "tenant")
     * @param string $tenant_user_id
     * @return void
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    protected function validateTenantUserExists(TenantUsersModel $tenantUsersModel, string $tenant_id, string $tenant_user_id): void
    {

        if (!$this->resourceExists($tenantUsersModel, $tenant_user_id, [
            [
                'tenant' => [
                    'eq' => $tenant_id
                ]
            ]
        ])) {
            throw new NotFoundException();
        }

    }

    /**
     * Does tenant user scoped resource exist?
     *
     * @param ResourceModel $resourceModel
     * @param string $tenant_user_id (With database column of "tenant_user")
     * @param string $resource_id
     * @return bool
     * @throws ApiServiceException
     */
    protected function tenantUserResourceExists(ResourceModel $resourceModel, string $tenant_user_id, string $resource_id): bool
    {

        return $this->resourceExists($resourceModel, $resource_id, [
            [
                'tenant_user' => [
                    'eq' => $tenant_user_id
                ]
            ]
        ]);

    }

    /**
     * Validate tenant user scoped resource exists with tenant user.
     *
     * @param ResourceModel $resourceModel
     * @param string $tenant_user_id (With database column of "tenant_user")
     * @param string $resource_id
     * @return void
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    protected function validateTenantUserResourceExists(ResourceModel $resourceModel, string $tenant_user_id, string $resource_id): void
    {

        if (!$this->tenantUserResourceExists($resourceModel, $tenant_user_id, $resource_id)) {
            throw new NotFoundException();
        }

    }

    /**
     * List tenant user resources.
     *
     * @param ResourceModel $resourceModel
     * @param string $tenant_user_id (With database column of "tenant_user")
     * @return array (Array returned from listResources method)
     * @throws ApiServiceException
     * @throws BadRequestException
     */
    protected function listTenantUserResources(ResourceModel $resourceModel, string $tenant_user_id): array
    {

        $query_filter = [
            [
                'tenant_user' => [
                    'eq' => $tenant_user_id
                ]
            ]
        ];

        return $this->listResources($resourceModel, $query_filter);

    }

}