# [API service](../README.md) > [Controllers](README.md) > PrivateApiController

The `PrivateApiController` extends [ApiController](apicontroller.md),
and emits the `api.controller.private` [event](../events.md) when instantiated.

The `rate_limit.private` [rate limit](../setup.md#configuration) is enforced.

Before the `PrivateApiController` is instantiated, a user must be identified using one of the identification methods
allowed in the [API configuration](../setup.md#configuration). The identified user is available within the controller
represented by a [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md) instance as `$this->user`.

User impersonation is made possible by setting the `X-Impersonate-User` header to the user ID to impersonate.
How this is handled depends on the [API configuration](../setup.md#configuration).

In addition, the `User` instance is placed into the [Bones service container](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/container.md) with alias `user`.