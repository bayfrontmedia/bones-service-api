# [API service](../README.md) > [Controllers](README.md) > UserKeys

- Extends [PrivateApiController](privateapicontroller.md)
- Implements [CrudControllerInterface](crudcontrollerinterface.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)
- [UserKeysModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/userkeys.md) available as `$this->userKeysModel`

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

`POST /users/{user}/keys`

**Path parameters:**

- `user`: (`required|uuid`)

**Required permissions:**

- Is admin, or self and `identity.key` config value equals `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [UserKeysModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/userkeys.md) fields

**Response:**

- HTTP status code: `201`
- Schema: [UserKeyResource](../schemas.md#userkeyresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## list

**Description:**

List resources.

**Route:**

`GET /users/{user}/keys`

**Path parameters:**

- `user`: (`required|uuid`)

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
- Schema: [UserKeyCollection](../schemas.md#userkeycollection)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## read

**Description:**

Read single resource.

**Route:**

`GET /users/{user}/keys/{id}`

**Path parameters:**

- `user`: (`required|uuid`)
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
- Schema: [UserKeyResource](../schemas.md#userkeyresource)

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

`PATCH /users/{user}/keys/{id}`

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

- [UserKeysModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/userkeys.md) fields

**Response:**

- HTTP status code: `200`
- Schema: [UserKeyResource](../schemas.md#userkeyresource)

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

`DELETE /users/{user}/keys/{id}`

**Path parameters:**

- `user`: (`required|uuid`)
- `id`: (`required|uuid`)

**Required permissions:**

- Is admin or self

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