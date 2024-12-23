# [API service](README.md) > Exceptions

Methods within an API controller should only throw a `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
(500 status code) or a `Bayfront\BonesService\Api\Exceptions\ApiHttpException` (400-499 status codes).
Since these both implement `Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface`,
they allow the HTTP status code to be updated accurately by the API service during the `bones.exception` event.

The `Bayfront\BonesService\Api\Exceptions\ApiServiceException` is a "catch all" exception
which returns a `500` status code.

The following exceptions exist in the `Bayfront\BonesService\Api\Exceptions\Http` namespace:

| Exception                   | Status code |
|-----------------------------|-------------|
| `BadRequestException`       | `400`       |
| `UnauthorizedException`     | `401`       |
| `PaymentRequiredException`  | `402`       |
| `ForbiddenException`        | `403`       |
| `NotFoundException`         | `404`       |
| `MethodNotAllowedException` | `405`       |
| `NotAcceptableException`    | `406`       |
| `ConflictException`         | `409`       |
| `TooManyRequestsException`  | `429`       |

These all extend `Bayfront\Bones\Exceptions\HttpException`, so they can be filtered as necessary.

For example, if exceptions are being logged from a `bones.exception` event subscription,
all `HttpException`'s thrown can be omitted from the normal exception handler by adding
the following condition:

```php
if (!$e instanceof HttpException) {
  // Log as normal
}
```

## Exception handler

To ensure all thrown exceptions return an `ErrorResource`, the [Bones exception handler](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/exceptions.md#exception-handler)
can use the `Bayfront\BonesService\Api\Utilities\ApiError::respond` method to respond to all thrown exceptions.

### respond

**Description:**

Respond with `ErrorResource`.

**Parameters:**

- `$response` (`Response`)
- `$e` (`Throwable`)
- `$links = []` (array)

The `ErrorResource` includes a `link` property which can be used to return a link to additional documentation regarding
the exception thrown. The array should be keyed by the exception code. 
If the exception code is `0`, the HTTP status code will be used.

For example:

```php
return [
    400 => 'https://example.com/docs/400',
    401 => 'https://example.com/docs/401',
    402 => 'https://example.com/docs/402',
    403 => 'https://example.com/docs/403',
    404 => 'https://example.com/docs/404',
    405 => 'https://example.com/docs/405',
    406 => 'https://example.com/docs/406',
    409 => 'https://example.com/docs/409',
    429 => 'https://example.com/docs/429',
    500 => 'https://example.com/docs/500'
];
```

**Returns:**

- (void)

**Example:**

Example from the Bones exception handler:

```php
public function respond(Response $response, Throwable $e): void
{
    if (App::isDebug()) { // Utilize the default Bones exception handler in debug mode for more detailed information
        parent::respond($response, $e);
    } else {
        ApiError::respond($response, $e, require(App::resourcesPath('/api/api-errors.php')));
    }

}
```