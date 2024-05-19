# API service

- [Initial setup](setup.md)
- [Events](events.md)
- [Filters](filters.md)
- [ApiService](apiservice-class.md)

## General usage

Besides events and filters, the API service interaction happens within controllers.

Any controller used by the API service must extend `Bayfront\BonesService\Api\Abstracts\ApiController`,
which implements an `Bayfront\BonesService\Api\Interfaces\ApiControllerInterface`.

The interface requires only one method, `isPrivate`, which returns a boolean value.
its value determines which of the API controller events are executed.

The [ApiService class](apiservice-class.md) is available within the controller as `$this->apiService`.

### Exceptions

Methods within an API controller should only throw a `Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface`.
This allows the HTTP status code to be updated accurately by the API service during the `bones.exception` event.

The `Bayfront\BonesService\Api\Exceptions\ApiServiceException` is a "catch all" exception 
which returns a `500` status code.

The following exceptions exist in the `Bayfront\BonesService\Api\Exceptions\Http` namespace:

| Exception                 | Status code |
|---------------------------|-------------|
| BadRequestException       | `400`       |
| UnauthorizedException     | `401`       |
| PaymentRequiredException  | `402`       |
| ForbiddenException        | `403`       |
| NotFoundException         | `404`       |
| MethodNotAllowedException | `405`       |
| NotAcceptableException    | `406`       |
| ConflictException         | `409`       |
| TooManyRequestsException  | `429`       |

These all extend `Bayfront\Bones\Exceptions\HttpException`, so they can be filtered as necessary.

For example, if exceptions are being logged from a `bones.exception` event subscription, 
all `HttpException`'s thrown can be omitted from the normal exception handler by adding 
the following condition:

```php
if ($e instanceof HttpException) {
  // Log as normal
}
```
