# [API service](../README.md) > [Traits](README.md) > TenantResource

The `TenantResource` trait is used when working with tenant-scoped resources

Methods include:

- [validateTenantExists](#validatetenantexists)
- [tenantResourceExists](#tenantresourceexists)
- [validateTenantResourceExists](#validatetenantresourceexists)
- [listTenantResources](#listtenantresources)

## validateTenantExists

**Description:**

Validate tenant exists.

**Parameters:**

- `$tenant_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## tenantResourceExists

**Description:**

Does tenant scoped resource exist?

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$tenant_id` (string): With database column of `tenant`
- `$resource_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`

## validateTenantResourceExists

**Description:**

Validate tenant scoped resource exists.

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$tenant_id` (string): With database column of `tenant`
- `$resource_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## listTenantResources

**Description:**

List tenant resources.

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$tenant_id` (string): With database column of `tenant`

**Returns:**

- (array): Array returned from [listResources](usesresourcemodel.md#listresources) method

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`