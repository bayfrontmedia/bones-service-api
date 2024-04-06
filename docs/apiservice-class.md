# API service > ApiService class

The `ApiService` class contains the following Bones services:

- [EventService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) as `$this->events`
- [FilterService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/filters.md) as `$this->filters`
- [Response](https://github.com/bayfrontmedia/bones/blob/master/docs/services/response.md) as `$this->response`

Methods include:

- [getConfig](#getconfig)
- [getBody](#getbody)
- [respond](#respond)
- [throwException](#throwexception)

## getConfig

**Description**

Get API configuration value in dot notation.

**Parameters**

- `$key = ''` (string): Key to return in dot notation
- `$default = null` (mixed): Default value to return if not existing

**Returns**

- (mixed)

**Throws**

- `ApiExceptionInterface`

## getBody

**Description**

Get JSON-encoded request body.

**Parameters**

- `$required = false` (bool): Throws `BadRequestException` if required and not existing

**Returns**

- (array)

**Throws**

- `ApiExceptionInterface`

## respond

**Description**

Send API response

- Filters response using the `api.response` filter
- Executes the `api.response` event
- Sends `$data` as JSON-encoded string

**Parameters**

- `$status_code = 200` (int): HTTP status code to send
- `$data = []` (array): Data to send
- `$headers = []` (array): Key/value pairs of header values to send

**Returns**

- (void)

**Throws**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`

## throwException

**Description**

Throws appropriate API exception based on status code.

This method is helpful for handling exceptions thrown from external sources.
As long as the exception's `code` is set, this method can be used to 
set the status code and throw an API exception. 

**Parameters**

- `$status_code` (int): HTTP status code
- `$message = ''` (string)
- `$previous = null` (Throwable)

**Returns**

- (void)

**Throws**

- `ApiExceptionInterface`

**Example**

```php
try {
    // Something
} catch (ExternalException $e) {
    $this->apiService->throwException($e->getCode(), $e->getMessage(), $e);
}
```