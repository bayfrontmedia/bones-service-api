# [API service](../README.md) > [Controllers](README.md) > PublicApiController

The `PublicApiController` extends [ApiController](apicontroller.md),
and emits the `api.controller.public` [event](../events.md) when instantiated.

The `rate_limit.public` [rate limit](../setup.md#configuration) is enforced.