# [API service](README.md) > Initial setup

- [Configuration](#configuration)
- [Add to container](#add-to-container)
- [Database migration and seeding](#database-migration-and-seeding)
- [Routes](#routes)
- [Setup events](#setup-events)
- [Scheduled jobs](#scheduled-jobs)
- [Exception handler](#exception-handler)

## Configuration

This service requires a configuration array.
Typically, this would be placed at `config/api.php`.

**Example:**

```php
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Rbac\RbacService;

return [
    'version' => '1.0.0', // API version
    'request' => [
        'headers' => [ // Required headers for every request
            'Accept' => 'application/json',
        ],
        'https_env' => [ // App environments to force HTTPS
            App::ENV_STAGING,
            App::ENV_QA,
            App::ENV_PROD,
        ],
        'id' => [ // Unique request ID
            'enabled' => true,
            'length' => 10,
        ],
        'ip_whitelist' => [], // Only allow requests from IPs (empty array to allow all)
        'meta' => [ // Allow requests for meta array to be returned
            'enabled' => true,
            'field' => 'meta',
            'env' => [
                App::ENV_DEV,
                App::ENV_STAGING,
                App::ENV_QA,
                App::ENV_PROD,
            ],
        ],
    ],
    'response' => [
        'headers' => [ // Required headers for every response
            'Content-Type' => 'application/json',
        ],
    ],
    'rate_limit' => [ // Rate limit (per minute), 0 for unlimited
        'auth' => App::environment() === App::ENV_DEV ? 0 : 3,
        'private' => App::environment() === App::ENV_DEV ? 0 : 200,
        'public' => App::environment() === App::ENV_DEV ? 0 : 10,
    ],
    'identity' => [ // Allowed identification methods
        'key' => true, // API key
        'token' => true, // Access token
    ],
    'auth' => [ // Allowed authentication methods
        'password' => [ // Authenticate with email + password
            'enabled' => true,
            'tfa' => [
                'enabled' => !(App::environment() === App::ENV_DEV),
                'wait' => 3, // Wait time (in minutes) to wait before creating a new TFA, or 0 to disable
                'duration' => 15, // Validity duration (in minutes), 0 for unlimited
                'length' => 6, // Value length
                'type' => RbacService::TOTP_TYPE_NUMERIC, // Value type
            ],
        ],
        'otp' => [ // Authenticate with email + OTP
            'enabled' => true,
            'wait' => 3, // Wait time (in minutes) to wait before creating a new TFA, or 0 to disable
            'duration' => 15, // Validity duration (in minutes), 0 for unlimited
            'length' => 6, // Value length
            'type' => RbacService::TOTP_TYPE_NUMERIC, // Value type
        ],
        'refresh' => [ // Authenticate using refresh token
            'enabled' => true,
        ],
    ],
    'meta' => [ // Meta validation rules in dot notation, or empty for none. Only these keys will be allowed.
        'tenant' => [
            'address.street' => 'isString|lengthLessThan:255',
            'address.street2' => 'isString|lengthLessThan:255',
            'address.city' => 'isString|lengthLessThan:255',
            'address.state' => 'isString|lengthLessThan:255',
            'address.zip' => 'isString|lengthLessThan:255',
        ],
        'user' => [
            'name.first' => 'required|isString|lengthLessThan:255',
            'name.last' => 'required|isString|lengthLessThan:255',
        ],
    ],
    'user' => [
        'allow_register' => false, // Allow public user registration?
        'allow_delete' => true, // Allow users to delete their own accounts?
        'impersonate' => [
            'enabled' => true, // Enable user impersonation?
            'admin_only' => true // Only admins can impersonate?
        ],
        'password_request' => [ // Password reset request
            'enabled' => true,
            'wait' => 3,
            'duration' => 15,
            'length' => 36,
            'type' => RbacService::TOTP_TYPE_ALPHANUMERIC,
        ],
        'unverified' => [
            'expiration' => 10080, // If RBAC users require verification, duration before unverified users are deleted (in minutes). 0 to disable. 10080 = 7 days
            'new_only' => true, // Remove only new unverified users? When false, all unverified users will be eligible for deletion
        ],
        'verification' => [ // User email verification
            'enabled' => true,
            'wait' => 3,
            'duration' => 1440,
            'length' => 36,
            'type' => RbacService::TOTP_TYPE_ALPHANUMERIC,
        ],
    ],
    'tenant' => [
        'allow_create' => false, // Allow non-admin users to create tenants?
        'auto_enabled' => true, // Enable tenants created by non-admin users?
        'allow_delete' => true, // Allow non-admin users to delete tenants they own?
        'user_meta' => [
            'manage_self' => true, // Allow tenant users to manage their own tenant_user_meta?
        ],
    ],
];
```

### Configuration summary

- `version`: The API version is added to the information returned by the `php bones about:bones` [console command](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/console.md).
It is also added to the returned meta array when `request.meta.enabled` is `true`.
- `request.headers`: API requests without these headers will return a `BadRequestException`.
- `request.https_env`: API requests not made over HTTPS in these environments will return a `NotAcceptableException`.
- `request.id`: When enabled, a unique `REQUEST_ID` constant is created in the `app.bootstrap` event which can be used
to identify each unique request as it is processed by the API. It is added to the `ErrorResource` schema as well as the
returned meta array when `request.meta.enabled` is `true`. The request ID is helpful to attach to any logging services
which may be used by the API.
- `request.ip_whitelist`: API requests made from an IP not found in this list will return a `ForbiddenException`.
- `request.meta`: When enabled, requests with the field value of `true` existing in the query will return an array of
metadata along with the response. For example, by adding `?meta=true` to the request, the response will include metadata.
This array can be filtered using the [api.response.meta filter](filters.md).
The `request.meta.env` array specifies which app environments to allow this functionality.
- `response.headers`: Headers to send with every API response.
- `rate_limit`: Define the rate limit for the `auth`, `private` and `public` API controllers, or `0` for unlimited.
- `identity`: Allow `key` and/or `token` identification methods when authorizing a `PrivateApiController` request.
The `key` method will check the `X-Api-Key` header for a valid user API key, and the `token` method will check the `Bearer`
header for a valid access token. (See [PrivateApiController](controllers/privateapicontroller.md))
- `auth.passsword`: Allow user to authenticate with email + password. The `auth.password.tfa` specifies whether to
issue a TFA (two-factor authentication) code, along with its rules.
- `auth.otp`: Allow user to authenticate with email + OTP (one-time password), along with its rules.
- `auth.refresh`: Allow user to authenticate with a valid refresh token.
- `meta`: The `meta.tenant` and `meta.user` keys allow for the definition of validation rules
enforced for different meta resources. Only defined keys will be allowed.
- `user.allow_register`: Allow public user registration
- `user.allow_delete`: Allow users to delete their own accounts
- `user.impersonate.enabled`: Enable user impersonation?
- `user.impersonate.admin_only`: Only admins can impersonate?
- `user.password_request`: Allow users to request a password reset, along with its rules.
- `user.unverified.expiration`: If RBAC users [require verification](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/setup.md#configuration),
  duration (in minutes) before unverified users are deleted via a scheduled job. `0` to disable.
- `user.unverified.new_only`: Remove only new unverified users? When `false`, all unverified users will be eligible for deletion.
- `user.verification`: Require users to verify their email addresses, along with its rules.
- `tenant.allow_create`: Allow non-admin users to create tenants?
- `tenant.auto_enabled`: Automatically enable tenants created by non-admin users?
- `tenant.allow_delete`: Allow non-admin users to delete tenants they own?
- `tenant.user_meta`: Allow tenant users to manage their own tenant user meta? If disabled, users must have the necessary
`tenant_user_meta:*` permission.

## Add to container

With the configuration completed, the [ApiService](apiservice-class.md) class needs to be added to the Bones [service container](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/container.md).
This is typically done in the `resources/bootstrap.php` file.
You may also wish to create an alias.

For more information, see [Bones bootstrap documentation](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/bootstrap.md).

The `ApiService` requires the following classes in its constructor:

- [EventService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md)
- [FilterService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/filters.md)
- [Response](https://github.com/bayfrontmedia/bones/blob/master/docs/services/response.md)
- [Cron](https://github.com/bayfrontmedia/bones/blob/master/docs/services/scheduler.md)
- [RbacService](https://github.com/bayfrontmedia/bones-service-rbac)

In addition, the API service makes use of the [Leaky Bucket](https://github.com/bayfrontmedia/leaky-bucket/) library, so a `Bayfront\LeakyBucket\AdapterInterface`
must also exist in the container.

By allowing the container to `make` the class during bootstrapping,
the API service is available to be used in console commands:

```php
$apiService = $container->make('Bayfront\BonesService\Api\ApiService', [
    'config' => (array)App::getConfig('api', [])
]);

$container->set('Bayfront\BonesService\Api\ApiService', $apiService);
$container->setAlias('apiService', 'Bayfront\BonesService\Api\ApiService');
```

## Database migration and seeding

Since the API service utilizes the RBAC service, the [RBAC service migration](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/setup.md#database-migration-and-seeding) must be run using:

```shell
php bones migrate:up
```

Initial database seeding with a verified admin user and all the necessary permissions can be done from the console:

```shell
php bones api:seed user@example.com password

# Force seeding (no input/confirmation required)
php bones api:seed user@example.com password --force
```

The password is optional. If not provided, one will be created automatically.

## Routes

The API service comes will all the routes necessary to utilize all of its features. 
The use of these predefined routes is optional. You can define your own routes and map them to any of the included
[controller methods](controllers/README.md) you wish.

To use the API service predefined routes, add the following to an `app.bootstrap` event subscription
when defining your routes:

```php
use Bayfront\BonesService\Api\Utilities\ApiRoutes

ApiRoutes::define($this->router, '/api/v1');
```

The `define` method accepts two parameters. The first is a `Router` instance, which is required.
The second is a route prefix which is automatically added to the beginning of all defined routes.

The API service does not include a route for the root URL. This can be handled at the app-level if desired.

If using any of the API service controllers, the router cannot utilize the `class_namespace` [config key](https://github.com/bayfrontmedia/bones/blob/master/docs/services/router.md)
since the API controllers reside in a different namespace than the controllers used at the app-level.

## Setup events

Some events used by this service would most likely need to dispatch messages. This must be setup on an app-level.

The suggested events are:

- `api.auth.otp`
- `api.auth.password.tfa`
- `api.user.password.request`
- `rbac.user.password.updated`
- `api.user.verification.request`
- `rbac.user.verified`
- `rbac.tenant.invitation.created`
- `rbac.tenant.invitation.accepted`

## Scheduled jobs

The API service handles all the [recommended scheduled jobs](https://github.com/bayfrontmedia/bones-service-rbac/blob/master/docs/setup.md#scheduled-jobs) 
by the RBAC service except pruning soft-deleted resources.

Therefore, the following scheduled jobs should be created at the app-level:

- Prune soft-deleted resources which no longer need to exist in the database using the [purgeTrashed](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/traits/softdeletes.md#purgetrashed) method.
- Delete expired buckets from storage.

The method to delete the expired buckets will vary based on which [storage adapter](https://github.com/bayfrontmedia/leaky-bucket/?tab=readme-ov-file#storage-adapter) is being used.
Here are some examples:

### Local

```php
$this->scheduler->call('delete-expired-buckets', function () {

    $files = glob(App::storagePath('/app/buckets/*')); // Path to where the buckets are stored

    $count = 0;

    foreach ($files as $file) {

        if (is_file($file)) {

            if (time() - filemtime($file) >= 60 * 60) { // 60 minutes
                $count++;
                unlink($file);
            }

        }
        
    }

});
```

### PDO

```php
$this->scheduler->call('delete-expired-buckets', function () {

    // Specify table used for buckets
    $this->db->query("DELETE FROM buckets WHERE updated_at < DATE_SUB(NOW(), INTERVAL 60 MINUTE)");

    $count = $this->db->rowCount();

});
```

## Exception handler

It is recommended to update the app's exception handler to use the [ApiError:respond method](exceptions.md#exception-handler) to ensure all exceptions
thrown will return an `ErrorResource`.