# [API service](../README.md) > [Controllers](README.md) > Tenants

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenants.md) available as `$this->tenantsModel`

Methods:

- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)

## create

**Description:**

Create new resource.

Non-admin users cannot define owner, domain or enabled values.
Domain is always transformed to a lowercase URL-friendly slug.

**Route:**

`POST /tenants`

**Path parameters:**

- (none)

**Required permissions:**

- Is admin or [tenant.allow_create config](../setup.md#configuration) is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenants.md) fields

If user is not an admin:

- `owner` is set to current user ID
- `domain` is set to the tenant name
- `enabled` is set depending on the [tenant.auto_enabled config](../setup.md#configuration) value

**Response:**

- HTTP status code: `201`
- Schema: [TenantResource](../schemas.md#tenantresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants`

**Path parameters:**

- (none)

**Required permissions:**

- Is admin or filtered to tenants the current user belongs to

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantCollection](../schemas.md#tenantcollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{id}`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or in tenant

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantResource](../schemas.md#tenantresource)

**Throws:**

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## update

**Description:**

Update existing resource.

Non-admin users cannot update domain or enabled values.
Domain is always transformed to a lowercase URL-friendly slug.
Owner cannot be set to user not already in tenant.

Update a `null` value to a meta key to be removed.

**Route:**

`PATCH /tenants/{id}`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- `tenant:update`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenants.md) fields

**Response:**

- HTTP status code: `200`
- Schema: [TenantResource](../schemas.md#tenantresource)

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

`DELETE /tenants/{id}`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or owns tenant and [tenant.allow_delete config](../setup.md#configuration) value is `true`

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