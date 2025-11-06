# [API service](../README.md) > [Traits](README.md) > TenantUserResource

The `TenantUserResource` trait is used when working with tenant user-scoped resources

Methods include:

- [validateTenantUserExists](#validatetenantuserexists)
- [tenantUserResourceExists](#tenantuserresourceexists)
- [validateTenantUserResourceExists](#validatetenantuserresourceexists)
- [listTenantUserResources](#listtenantuserresources)

## validateTenantUserExists

**Description:**

Validate tenant user exists in tenant.

**Parameters:**

- `$tenantUsersModel` (`TenantUsersModel`)
- `$tenant_id` (string)
- `$tenant_user_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## tenantUserResourceExists

**Description:**

Does tenant user scoped resource exist?

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$tenant_user_id` (string): With database column of `tenant_user`
- `$resource_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`

## validateTenantUserResourceExists

**Description:**

Validate tenant user scoped resource exists with tenant user.

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$tenant_user_id` (string): With database column of `tenant_user`
- `$resource_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## listTenantUserResources

**Description:**

List tenant user resources.

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$tenant_user_id` (string): With database column of `tenant_user`

**Returns:**

- (array): Array returned from [listResources](usesresourcemodel.md#listresources) method

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`