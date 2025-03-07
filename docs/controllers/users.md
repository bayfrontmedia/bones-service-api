# [API service](../README.md) > [Controllers](README.md) > Users

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [UsersModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/users.md) available as `$this->usersModel`

Methods:

- [logout](#logout)
- [me](#me)
- [tenantPermissions](#tenantpermissions)
- [create](#create)
- [list](#list)
- [read](#read)
- [update](#update)
- [delete](#delete)
- [listInvitations](#listinvitations)
- [acceptInvitation](#acceptinvitation)
- [listTenants](#listtenants)

## logout

**Description:**

Revoke access and refresh keys for current user.
Users will still be able to authenticate with an API key
or if access tokens are not revocable.

**Route:**

`POST /users/logout`

**Path parameters:**

- (none)

**Required permissions:**

- (none)

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

## me

**Description:**

Read current user.

**Route:**

`GET /users/me`

**Path parameters:**

- (none)

**Required permissions:**

- (none)

**Required headers:**

- (none)

**Valid query parameters:**

- (none)

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [UserResource](../schemas.md#userresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## tenantPermissions

**Description:**

List all user permissions in tenant.

NOTE: This method returns all permissions and ignores
any URL query parameters which may exist.

**Route:**

`GET /users/me/tenant/{:tenant}/permissions`

**Path parameters:**

- `tenant`: (`required|uuid`)

**Required permissions:**

- Is admin or in tenant

**Required headers:**

- (none)

**Valid query parameters:**

- (none)

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [PermissionCollection](../schemas.md#permissioncollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## create

**Description:**

Create new resource.

**Route:**

`POST /users`

**Path parameters:**

- (none)

**Required permissions:**

- Is admin

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [UsersModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/users.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [UserResource](../schemas.md#userresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## list

**Description:**

List resources.

**Route:**

`GET /users`

**Path parameters:**

- (none)

**Required permissions:**

- Is admin

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [UserCollection](../schemas.md#usercollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /users/{id}`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [UserResource](../schemas.md#userresource)

**Throws:**

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## update

**Description:**

Update existing resource.

Update a `null` value to a meta key to be removed.

**Route:**

`PATCH /users/{id}`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [UsersModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/users.md) fields

Non-admin users cannot define `admin` or `enabled` fields.

**Response:**

- HTTP status code: `200`
- Schema: [UserResource](../schemas.md#userresource)

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

`DELETE /users/{id}`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self if `user.allow.delete` [configuration value](../setup.md#configuration) is `true`.

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

## listInvitations

**Description:**

List user's tenant invitations.

**Route:**

`GET /users/{id}/invitations`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self

**Required headers:**

- (none)

**Valid query parameters:**

- Any valid [QueryParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) keys

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [TenantInvitationCollection](../schemas.md#tenantinvitationcollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## acceptInvitation

**Description:**

Accept tenant invitation.

**Route:**

`POST /users/{user}/invitations/{id}`

**Path parameters:**

- `user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self

**Required headers:**

- `Content-Type`: `application/json`

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

## listTenants

**Description:**

List tenants user belongs to.
Admins belong to all tenants.

**Route:**

`GET /users/{id}/tenants`

**Path parameters:**

- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self

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
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`