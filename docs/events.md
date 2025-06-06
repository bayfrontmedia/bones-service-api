# [API service](README.md) > Events

The following [events](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) are added by this service:

- `api.start`: Executes in the `ApiService` constructor as the first event available to this service. 
The `ApiService` instance is passed as a parameter.
- `api.auth.limit`: Executes when a rate limit has been exceeded. 
- `api.auth.otp`: Executes when a user attempts to authenticate with a correct email and an OTP has been generated.
A [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md) and [Totp](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/totp.md) instance are passed as parameters.
- `api.auth.otp.fail`: Executes when a user fails to authenticate using their email + OTP. The email address used
is passed as a parameter.
- `api.auth.password.fail`: Executes when a user fails to authenticate using email + password. The email address used
is passed as a parameter.
- `api.auth.password.tfa`: Executes when a user attempts to authenticate with a correct email + password and a 
TFA code has been generated. A [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md) and
[Totp](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/totp.md) instance are passed as parameters.
- `api.auth.refresh.fail`: Executes when a user fails to authenticate using a refresh token.
- `api.auth.tfa.fail`: Executes when a user fails to authenticate using email + TFA. The email address used is passed
as a parameter.
- `api.auth.success`: Executes when a user successfully authenticates. A [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md)
instance is passed as a parameter.
- `api.controller`: Executes when any `ApiController` is instantiated. The controller instance is passed as a parameter.
- `api.controller.auth`: Executes when an `AuthApiController` is instantiated. The controller instance is passed as a parameter.
- `api.controller.private`: Executes when a `PrivateApiController` is instantiated. The controller instance is passed as a parameter.
- `api.controller.public`: Executes when a `PublicApiController` is instantiated. The controller instance is passed as a parameter.
- `api.response`: Executes just before the API response is sent with the `respond` method. 
The `ApiController` instance is passed as a parameter.
- `api.user.impersonate`: Executes when a user is being impersonated. Two [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md)
instances are passed as parameters: The first representing the actual user, and the second representing the user being impersonated.
- `api.user.password.fail`: Executes when a user fails to reset their password using an incorrect token. The email address
used is passed as a parameter.
- `api.user.password_request`: Executes when a user requests a password reset. 
A [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md) and [Totp](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/totp.md) instance are passed as parameters.
- `api.user.password_request.fail`: Executes when a user fails to request a password reset. This is typically because
sufficient time has not yet elapsed since the last request was made. The email address used is passed as a parameter.
- `api.user.verification_request`: Executes when a user verification request is created.
A [User](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/user.md) and [Totp](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/totp.md) instance are passed as parameters.
- `api.user.verification_request.fail`: Executes when a user fails to request a new email verification. 
This is typically because the user is already verified, or sufficient time has not yet elapsed since the last request was made.
The email address used is passed as a parameter.
- `api.user.verification.fail`: Executes when a user fails to verify their email using their email and token. The email
address used is passed as a parameter.