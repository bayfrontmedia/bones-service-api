# [API service](../README.md) > [Controllers](README.md) > TenantPermissions

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantPermissionsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantpermissions.md) available as `$this->tenantUsersModel`

Methods:

- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)
- [listRoles](#listroles)

## create

**Description:**

Create new resource.

**Route:**

`POST /tenants/{tenant}/permissions`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- Is admin

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantPermissionsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantpermissions.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantPermissionResource](../schemas.md#tenantpermissionresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/permissions`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_permissions:read` or filtered to user permissions

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantPermissionCollection](../schemas.md#tenantpermissioncollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/permissions/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_permissions:read` or user has permission

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantPermissionResource](../schemas.md#tenantpermissionresource)

**Throws:**

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## update

**Description:**

Non-routed (relationship)

## delete

**Description:**

Delete single resource.

**Route:**

`DELETE /tenants/{tenant}/permissions/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- Is admin

**Required headers:**

- (none)

**Valid query parameters:**

- (none)

**Body:**

- (none)

**Response:**

- HTTP status code: `204`
- Schema: (none)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## listRoles

**Description:**

List roles with tenant permission.

**Route:**

`GET /tenants/{tenant}/permissions/{id}/roles`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_permissions:read`
- `tenant_roles:read`

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantRoleCollection](../schemas.md#tenantrolecollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`