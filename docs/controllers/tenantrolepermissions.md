# [API service](../README.md) > [Controllers](README.md) > TenantRolePermissions

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantRolePermissionsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantrolepermissions.md) available as `$this->tenantRolePermissionsModel`

Methods:

- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)

## create

**Description:**

Create new resource.

**Route:**

`POST /tenants/{tenant}/roles/{role}/permissions`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `role`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:update`
- `tenant_permissions:read`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantRolePermissionsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantrolepermissions.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantRolePermissionResource](../schemas.md#tenantrolepermissionresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/roles/{role}/permissions`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `role`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` and `tenant_permissions:read` or filtered to has role

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantRolePermissionCollection](../schemas.md#tenantrolepermissioncollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/roles/{role}/permissions/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `role`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` and `tenant_permissions:read` or has role

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantRolePermissionResource](../schemas.md#tenantrolepermissionresource)

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

`DELETE /tenants/{tenant}/roles/{role}/permissions/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `role`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:update`

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