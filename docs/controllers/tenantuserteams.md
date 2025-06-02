# [API service](../README.md) > [Controllers](README.md) > TenantUserTeams

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantUserTeamsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantuserteams.md) available as `$this->tenantUserTeamsModel`

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

`POST /tenants/{tenant}/users/{tenant_user}/teams`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)

**Required permissions:**

- `tenant_users:update`
- `tenant_user_teams:update`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantUserTeamsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantuserteams.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantUserTeamResource](../schemas.md#tenantuserteamresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/users/{tenant_user}/teams`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:read` or self

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserTeamCollection](../schemas.md#tenantuserteamcollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/users/{tenant_user}/teams/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:read` or self

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantUserTeamResource](../schemas.md#tenantuserteamresource)

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

`DELETE /tenants/{tenant}/users/{tenant_user}/teams/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `tenant_user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_users:update`
- `tenant_user_teams:update`

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