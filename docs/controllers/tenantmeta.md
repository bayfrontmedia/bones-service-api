# [API service](../README.md) > [Controllers](README.md) > TenantMeta

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantmeta.md) available as `$this->tenantMetaModel`

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

`POST /tenants/{tenant}/meta`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_meta:create`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantmeta.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantMetaResource](../schemas.md#tenantmetaresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/meta`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_meta:read`

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantMetaCollection](../schemas.md#tenantmetacollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/meta/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_meta:read`

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantMetaResource](../schemas.md#tenantmetaresource)

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

`PATCH /tenants/{tenant}/meta/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_meta:update`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantmeta.md) fields

**Response:**

- HTTP status code: `200`
- Schema: [TenantMetaResource](../schemas.md#tenantmetaresource)

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

`DELETE /tenants/{tenant}/meta/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_meta:delete`

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