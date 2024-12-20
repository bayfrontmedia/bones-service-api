# [API service](../README.md) > [Controllers](README.md) > Server

- Extends [PublicApiController](publicapicontroller.md)

Methods:

- [status](#status)

## status

**Description:**

Get server status.

**Route:**

`GET /server/status`

**Path parameters:**

- (none)

**Required permissions:**

- (none)

**Required headers:**

- `Content-Type`: `application/json`

**Valid query parameters:**

- (none)

**Body:**

- (none)

**Response:**

- HTTP status code: `200`
- Schema: [ServerStatusResource](../schemas.md#serverstatusresource)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException`