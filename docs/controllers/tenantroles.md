# [API service](../README.md) > [Controllers](README.md) > TenantRoles

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantRolesModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantroles.md) available as `$this->tenantRolesModel`

Methods:

- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)
- [listUsers](#listusers)

## create

**Description:**

Create new resource.

**Route:**

`POST /tenants/{tenant}/roles`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:create`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantRolesModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantroles.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantRoleResource](../schemas.md#tenantroleresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/roles`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` or filtered to user roles

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

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/roles/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` or has role

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantRoleResource](../schemas.md#tenantroleresource)

**Throws:**

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## update

**Description:**

Update existing resource.

**Route:**

`PATCH /tenants/{tenant}/roles/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:update`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantRolesModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantroles.md) fields

**Response:**

- HTTP status code: `200`
- Schema: [TenantRoleResource](../schemas.md#tenantroleresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## delete

**Description:**

Delete single resource.

**Route:**

`DELETE /tenants/{tenant}/roles/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:delete`

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

## listUsers

**Description:**

List tenant users who have role.

**Route:**

`GET /tenants/{tenant}/roles/{id}/users`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` or has role

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
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`