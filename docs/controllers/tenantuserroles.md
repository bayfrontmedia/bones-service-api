# [API service](../README.md) > [Controllers](README.md) > TenantUserRoles

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantUserRolesModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantuserroles.md) available as `$this->tenantUserRolesModel`

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

`POST /tenants/{tenant}/users/{tenant_user}/roles`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)

**Required permissions:**

- `tenant_users:update`
- `tenant_roles:update`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantUserRolesModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantuserroles.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantUserRoleResource](../schemas.md#tenantuserroleresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/users/{tenant_user}/roles`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` or self

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserRoleCollection](../schemas.md#tenantuserrolecollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/users/{tenant_user}/roles/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_roles:read` or self

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserRoleResource](../schemas.md#tenantuserroleresource)

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

`DELETE /tenants/{tenant}/users/{tenant_user}/roles/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_users:update`
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