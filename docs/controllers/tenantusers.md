# [API service](../README.md) > [Controllers](README.md) > TenantUsers

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantUsersModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantusers.md) available as `$this->tenantUsersModel`

Methods:

- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)
- [listPermissions](#listpermissions)

## create

**Description:**

Create new resource.

**Route:**

`POST /tenants/{tenant}/users`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- Is admin

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantUsersModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantusers.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantUserResource](../schemas.md#tenantuserresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/users`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- In tenant or admin

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserCollection](../schemas.md#tenantusercollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/users/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- In tenant or admin

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserResource](../schemas.md#tenantuserresource)

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

`DELETE /tenants/{tenant}/users/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_users:delete`

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

## listPermissions

**Description:**

List tenant user permissions.

**Route:**

`GET /tenants/{tenant}/users/{id}/permissions`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read`

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [PermissionCollection](../schemas.md#permissioncollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`