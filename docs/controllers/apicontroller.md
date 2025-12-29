# [API service](../README.md) > [Controllers](README.md) > ApiController

The `ApiController` is the widest scoped controller used by the API service. All other controllers extend this.
It requires an [ApiService](../apiservice-class.md) instance to be passed to the constructor, 
and emits the `api.controller` [event](../events.md) when instantiated.
No rate limits are enforced.

The `ApiController` contains the following services:

- [ApiService](../apiservice-class.md) as `$this->apiService`
- [EventService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) as `$this->events`
- [FilterService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/filters.md) as `$this->filters`
- [Response](https://github.com/bayfrontmedia/bones/blob/master/docs/services/response.md) as `$this->response`

Properties include:

- [check_required_headers](#check_required_headers)
- [check_https](#check_https)
- [check_ip_whitelist](#check_ip_whitelist)
- [set_required_headers](#set_required_headers)

Methods include:

- [identifyUser](#identifyuser)
- [enforceRateLimit](#enforceratelimit)
- [validateIsAdmin](#validateisadmin)
- [validateInEnabledTenant](#validateinenabledtenant)
- [validateHasPermissions](#validatehaspermissions)
- [validatePath](#validatepath)
- [validateQuery](#validatequery)
- [validateHeaders](#validateheaders)
- [validateHasBody](#validatehasbody)
- [validateFieldsExist](#validatefieldsexist)
- [validateFieldsDoNotExist](#validatefieldsdonotexist)
- [getPostData](#getpostdata)
- [getFormEncodedBody](#getformencodedbody)
- [getJsonBody](#getjsonbody)
- [getTextBody](#gettextbody)
- [respond](#respond)

## check_required_headers

Boolean. Default `true`. When `false`, the [required request headers](../setup.md#configuration) will not be checked.

## check_https

Boolean. Default `true`. When `false`, HTTPS [will not be enforced](../setup.md#configuration).

## check_ip_whitelist

Boolean. Default `true`. When `false`, the IP whitelist [will not be enforced](../setup.md#configuration).

## set_required_headers

Boolean. Default `true`. When `false`, the [required response headers](../setup.md#configuration) will not be set.

## identifyUser

**Description:**

Identify user using one of the identification methods allowed in the [API configuration](../setup.md#configuration) `identity` array
using one of the following headers:

- `Bearer`: Identify with token
- `X-Api-Key`: Identify with key

User impersonation is made possible by setting the `X-Impersonate-User` header to the user ID to impersonate.
How this is handled depends on the [API configuration](../setup.md#configuration).

In addition, the `User` instance is placed into the [Bones service container](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/container.md) with alias `user`.

**Parameters:**

- (None)

**Returns:**

- (`\Bayfront\BonesService\Rbac\User`)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## enforceRateLimit

**Description:**

Enforce rate limit and set `X-RateLimit` headers.

- `X-RateLimit-Limit`: Rate limit (per minute)
- `X-RateLimit-Remaining`: Requests remaining
- `X-RateLimit-Reset`: Seconds until rate limit is completely reset
- `Retry-After`: Seconds to wait until another request can be made. Only sent when rate limit has been exceeded.

**Parameters:**

- `$id` (string): Bucket ID
- `$limit` (int)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException`

## validateIsAdmin

**Description:**

Validate user is admin.

**Parameters:**

- `$user` ([User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md))

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## validateInEnabledTenant

**Description:**

Validate user is in enabled tenant.
Admin users have no restrictions.

**Parameters:**

- `$user` ([User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md))
- `$tenant_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## validateHasPermissions

**Description:**

Validate user has required permissions.
Admin users have no restrictions.

**Parameters:**

- `$user` ([User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md))
- `$tenant_id` (string)
- `$permission_names` (array)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException`

## validatePath

**Description:**

Validate path parameters against a defined set of rules.

**Parameters:**

- `$params` (array)
- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## validateQuery

**Description:**

Validate query against a defined set of rules.

NOTE: Since the query is a string, only string related validation rules can be applied.

**Parameters:**

- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md)
- `$allow_other = false` (bool): Allow other keys not defined in rules

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## validateHeaders

**Description:**

Validate headers against a defined set of rules.

**Parameters:**

- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## validateHasBody

**Description:**

Validate body content exists.

**Parameters:**

- (none)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## validateFieldsExist

**Description:**

Validate array contains all fields.
Helpful when validation rules do not include "required", such as with a `ResourceModel`.

**Parameters:**

- `$array` (array)
- `$keys` (array)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## validateFieldsDoNotExist

**Description:**

Validate array does not contain any fields.

**Parameters:**

- `$array` (array)
- `$keys` (array)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## getFormEncodedBody

**Description:**

Validate and return form URL encoded body.

**Parameters:**

- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md)
- `$allow_other = false` (bool): Allow other keys not defined in rules

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## getPostData

**Description:**

Validate and return `POST` data.

Since `POST` data is received as a string, the `$cast_fields` array
allows fields to be cast to another expected type before
processing rules.

Types include:

- `array` (From JSON object)
- `boolean`
- `float`
- `integer`
- `null` (If empty string)

**Parameters:**

- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md)
- `$allow_other = false` (bool): Allow other keys not defined in rules
- `$cast_fields = []` (array): Key/value pair of field/type

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## getJsonBody

**Description:**

Validate and return JSON body.

**Parameters:**

- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md)
- `$allow_other = false` (bool): Allow other keys not defined in rules

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## getTextBody

**Description:**

Validate and return plaintext body.

**Parameters:**

- `$rules` (array): [Validator rules](https://github.com/bayfrontmedia/php-validator/blob/master/docs/validator.md) with key of `body`

**Returns:**

- (string)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## respond

**Description:**

Send API response.

- Filters response using the [api.response](../filters.md) filter
- Triggers the [api.response](../events.md) event
- Sends `$data` as JSON encoded string

**Parameters:**

- `$status_code = 200` (int): HTTP status code to send
- `$data = []` (array): Data to send
- `$headers = []` (array): Key/value pairs of headers to send

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`