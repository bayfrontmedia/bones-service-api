# [API service](../README.md) > [Controllers](README.md) > Auth

- Extends [AuthApiController](authapicontroller.md)

Authentication errors are purposely kept nonspecific so as not to reveal unnecessary account information.

Methods:

- [login](#login)
- [createOtp](#createotp)
- [verifyTfa](#verifytfa)
- [refresh](#refresh)

## login

**Description:**

Authenticate with email + password.

**Route:**

`POST /auth/login`

**Path parameters:**

- (none)

**Required permissions:**

- If [auth.password.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `email` (string): Required
- `password` (string): Required

**Response:**

If [auth.password.tfa.enabled config](../setup.md#configuration) value is `true`:

- HTTP status code: `204`
- Schema: (none)
- Executes `api.auth.password.tfa` [event](../events.md)

Else:

- HTTP status code: `201`
- Schema: [AuthResource](../schemas.md#authresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`

## createOtp

**Description:**

Initiate authentication by creating OTP.
Executes `api.auth.otp` [event](../events.md).

**Route:**

`POST /auth/otp`

**Path parameters:**

- (none)

**Required permissions:**

- If [auth.otp.enabled config](../setup.md#configuration) value is `true`

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

## verifyTfa

**Description:**

Authenticate by verifying TFA token.

**Route:**

`POST /auth/tfa`

**Path parameters:**

- (none)

**Required permissions:**

- If [auth.password.tfa.enabled config](../setup.md#configuration) value
or [auth.otp.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `email` (string): Required
- `token` (string): Required

**Response:**

- HTTP status code: `201`
- Schema: [AuthResource](../schemas.md#authresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`

## refresh

**Description:**

Authenticate with refresh token.

**Route:**

`POST /auth/refresh`

**Path parameters:**

- (none)

**Required permissions:**

- If [auth.refresh.enabled config](../setup.md#configuration) value is `true`

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- `refresh_token` (string): Required

**Response:**

- HTTP status code: `201`
- Schema: [AuthResource](../schemas.md#authresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`
- `Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException`