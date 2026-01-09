# [API service](../README.md) > [Controllers](README.md) > PrivateApiController

The `PrivateApiController` extends [ApiController](apicontroller.md),
and emits the `api.controller.private` [event](../events.md) when instantiated.

The `rate_limit.private` [rate limit](../setup.md#configuration) is enforced.

Before the `PrivateApiController` is instantiated, a user must be identified using the [identifyUser](apicontroller.md#identifyuser) method.
The identified user is available within the controller represented by a [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md) instance as `$this->user`.