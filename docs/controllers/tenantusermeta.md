# [API service](../README.md) > [Controllers](README.md) > TenantUserMeta

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantUserMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantusermeta.md) available as `$this->tenantUserMetaModel`

Methods:

- [upsert](#upsert)
- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)

## upsert

**Description:**

Upsert tenant user meta.

Returned resource will have a new ID if previously existing.

**Route:**

`PUT /tenants/{tenant}/users/{tenant_user}/meta/{key}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `key`: (`required`)

**Required permissions:**

- `tenant_user_meta:create` or self if [tenant.user_meta_manage_self config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantUserMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantusermeta.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantUserMetaResource](../schemas.md#tenantusermetaresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## create

**Description:**

Create new resource.

**Route:**

`POST /tenants/{tenant}/users/{tenant_user}/meta`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)

**Required permissions:**

- `tenant_user_meta:create` or self if [tenant.user_meta_manage_self config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantUserMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantusermeta.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantUserMetaResource](../schemas.md#tenantusermetaresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/users/{tenant_user}/meta`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)

**Required permissions:**

- `tenant_user_meta:read` or self if [tenant.user_meta_manage_self config](../setup.md#configuration) value is `true`

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserMetaCollection](../schemas.md#tenantusermetacollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/users/{tenant_user}/meta/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_user_meta:read` or self if [tenant.user_meta_manage_self config](../setup.md#configuration) value is `true`

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserMetaResource](../schemas.md#tenantusermetaresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## update

**Description:**

Update existing resource.

**Route:**

`PATCH /tenants/{tenant}/users/{tenant_user}/meta/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_user_meta:update` or self if [tenant.user_meta_manage_self config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantUserMetaModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantusermeta.md) fields

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserMetaResource](../schemas.md#tenantusermetaresource)

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

`DELETE /tenants/{tenant}/users/{tenant_user}/meta/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_user_meta:delete` or self if [tenant.user_meta_manage_self config](../setup.md#configuration) value is `true`

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
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`