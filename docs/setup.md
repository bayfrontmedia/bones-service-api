# API service > Initial setup

- [Configuration](#configuration)
- [Add to container](#add-to-container)

## Configuration

This service requires a configuration array.
Typically, this would be placed at `config/api.php`.

**Example:**

```php
use Bayfront\Bones\Application\Utilities\App;

return [
    'version' => '1.0.0', // API version
    'request' => [
        'headers' => [ // Required headers for every request
            'Accept' => 'application/json'
        ],
        'https_env' => [ // App environments to force HTTPS
            App::ENV_STAGING,
            App::ENV_QA,
            App::ENV_PROD
        ],
        'ip_whitelist' => [] // Only allow requests from IPs (empty array to allow all)
    ],
    'response' => [
        'headers' => [ // Required headers for every response
            'Content-Type' => 'application/json'
        ]
    ]
];
```

The configuration rules are enforced by event subscriptions automatically added by the API service.

The API `version` is added to the information returned by the `php bones about:bones` [console command](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/console.md).

## Add to container

With a configuration completed, the `ApiService` class needs to be added to the Bones [service container](https://github.com/bayfrontmedia/bones/blob/master/docs/usage/container.md).
You may also wish to create an alias.

To ensure it only gets instantiated when needed, the container can `set` the class:

```php
use Bayfront\Bones\Application\Utilities\App;

$container->set('Bayfront\BonesService\Api\ApiService', function (Container $container) {

    return $container->make('Bayfront\BonesService\Api\ApiService', [
        'config' => (array)App::getConfig('api', [])
    ]);

});

$container->setAlias('apiService', 'Bayfront\BonesService\Api\ApiService');
```

However, by always instantiating the class during bootstrapping,
the API service is available to be used in console commands:

```php
$apiService = $container->make('Bayfront\BonesService\Api\ApiService', [
    'config' => (array)App::getConfig('api', [])
]);

$container->set('Bayfront\BonesService\Api\ApiService', $apiService);
$container->setAlias('apiService', 'Bayfront\BonesService\Api\ApiService');
```