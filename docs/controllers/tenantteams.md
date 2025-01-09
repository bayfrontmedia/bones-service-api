# [API service](../README.md) > [Controllers](README.md) > TenantTeams

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [TenantTeamsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantteams.md) available as `$this->tenantTeamsModel`

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

`POST /tenants/{tenant}/teams`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:create`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantTeamsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantteams.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [TenantTeamResource](../schemas.md#tenantteamresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /tenants/{tenant}/teams`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:read` or filtered to in team

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantTeamCollection](../schemas.md#tenantteamcollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## read

**Description:**

Read single resource.

**Route:**

`GET /tenants/{tenant}/teams/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:read` or in team

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantTeamResource](../schemas.md#tenantteamresource)

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

`PATCH /tenants/{tenant}/teams/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:update`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [TenantTeamsModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/tenantteams.md) fields

**Response:**

- HTTP status code: `200`
- Schema: [TenantTeamResource](../schemas.md#tenantteamresource)

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

`DELETE /tenants/{tenant}/teams/{id}`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:delete`

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

List tenant users who belong to team.

**Route:**

`GET /tenants/{tenant}/teams/{id}/users`

**Path parameters:**

- `tenant`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- `tenant_teams:read` or in team

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