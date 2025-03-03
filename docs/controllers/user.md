# [API service](../README.md) > [Controllers](README.md) > User

- Extends [AuthApiController](authapicontroller.md)
- Uses [UsesResourceModel](../traits/usesresourcemodel.md)

Methods:

- [register](#register)
- [passwordRequest](#passwordrequest)
- [resetPassword](#resetpassword)
- [verificationRequest](#verificationrequest)
- [verify](#verify)

## register

**Description:**

Register new user.

**Route:**

`POST /user/register`

**Path parameters:**

- (none)

**Required permissions:**

- If [user.allow_register config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- [UsersModel](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/models/users.md) fields

`admin` field is set to `false` and `enabled` field is set to `true`.

**Response:**

- HTTP status code: `201`
- Schema: [UserResource](../schemas.md#userresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## passwordRequest

**Description:**

Request password reset token.
Executes `api.user.password_request` event.

**Route:**

`POST /user/password-request`

**Path parameters:**

- (none)

**Required permissions:**

- If [user.password_request.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `email` (string): Required

**Response:**

- HTTP status code: `204`
- Schema: (none)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`

## resetPassword

**Description:**

Reset password.
Executes `rbac.user.password.updated` event.

**Route:**

`POST /user/password`

**Path parameters:**

- (none)

**Required permissions:**

- If [user.password_request.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `email` (string): Required
- `password` (string): Required
- `token` (string): Required

**Response:**

- HTTP status code: `204`
- Schema: (none)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`










## verificationRequest

**Description:**

Request new user verification token.
Executes `api.user.verification_request` event.

**Route:**

`POST /user/verification-request`

**Path parameters:**

- (none)

**Required permissions:**

- If [user.verification.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `email` (string): Required

**Response:**

- HTTP status code: `204`
- Schema: (none)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`

## verify

**Description:**

Verify new user verification token.
Executes `rbac.user.verified` event.

**Route:**

`POST /user/verify`

**Path parameters:**

- (none)

**Required permissions:**

- If [user.verification.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `email` (string): Required
- `token` (string): Required

**Response:**

- HTTP status code: `204`
- Schema: (none)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`