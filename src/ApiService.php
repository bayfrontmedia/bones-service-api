<?php

namespace Bayfront\BonesService\Api;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Service;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Api\Controllers\Auth\Auth;
use Bayfront\BonesService\Api\Controllers\Auth\User;
use Bayfront\BonesService\Api\Controllers\Private\Permissions;
use Bayfront\BonesService\Api\Controllers\Private\TenantRoles;
use Bayfront\BonesService\Api\Controllers\Private\Users;
use Bayfront\BonesService\Api\Controllers\Public\Server;
use Bayfront\BonesService\Api\Events\ApiServiceDevEvents;
use Bayfront\BonesService\Api\Events\ApiServiceEvents;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Filters\ApiServiceFilters;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\CronScheduler\Cron;
use Bayfront\HttpResponse\Response;
use Bayfront\RouteIt\Router;

class ApiService extends Service
{

    public EventService $events;
    public FilterService $filters;
    public RbacService $rbacService;
    public Response $response;
    protected array $config;

    /**
     * The container will resolve any dependencies.
     * EventService is required by the abstract service.
     *
     * @param EventService $events
     * @param FilterService $filters
     * @param RbacService $rbacService
     * @param Response $response
     * @param Cron $scheduler
     * @param array $config
     * @throws ApiServiceException
     */

    public function __construct(EventService $events, FilterService $filters, RbacService $rbacService, Response $response, Cron $scheduler, array $config)
    {
        $this->events = $events;
        $this->filters = $filters;
        $this->rbacService = $rbacService;
        $this->response = $response;
        $this->config = $config;

        parent::__construct($events);

        // Enqueue events

        try {

            $this->events->addSubscriptions(new ApiServiceEvents($this, $scheduler));

            if (in_array(App::environment(), $this->getConfig('dev_events', []))) {
                $this->events->addSubscriptions(new ApiServiceDevEvents($this));
            }

        } catch (ServiceException $e) {
            throw new ApiServiceException('Unable to start ApiService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        // Enqueue filters

        try {
            $this->filters->addSubscriptions(new ApiServiceFilters($this));
        } catch (ServiceException $e) {
            throw new ApiServiceException('Unable to start ApiService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $this->events->doEvent('api.start', $this);

    }

    /**
     * Get API configuration value in dot notation.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $key = '', mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * Add default API service routes.
     *
     * TODO:
     * Possibly condense the rules
     * and move to static utility class.
     *
     * Can rename ApiError to ApiUtilities?
     *
     * @param Router $router
     * @return void
     */
    public function addRoutes(Router $router): void
    {
        $router
            // Server
            ->get('/server/status', [Server::class, 'status'])
            ->get('/server/oas', [Server::class, 'oas'])
            // Auth
            ->post('/auth/login', [Auth::class, 'login'])
            ->post('/auth/otp', [Auth::class, 'otp'])
            ->post('/auth/tfa', [Auth::class, 'tfa'])
            ->post('/auth/refresh', [Auth::class, 'refresh'])
            // User
            ->post('/user/register', [User::class, 'register'])
            ->post('/user/password-request', [User::class, 'passwordRequest'])
            ->post('/user/password', [User::class, 'password'])
            ->post('/user/verification-request', [User::class, 'verificationRequest'])
            ->post('/user/verification', [User::class, 'verification'])
            // Permissions
            ->post('/permissions', [Permissions::class, 'create'])
            ->get('/permissions', [Permissions::class, 'list'])
            ->get('/permissions/{*:id}', [Permissions::class, 'read'])
            ->patch('/permissions/{*:id}', [Permissions::class, 'update'])
            ->delete('/permissions/{*:id}', [Permissions::class, 'delete'])
            // Users
            ->get('/users/logout', [Users::class, 'logout'])
            ->get('/users/me', [Users::class, 'me'])
            ->get('/users/{*:id}', [Users::class, 'read'])
            // Tenant roles
            ->post('/tenants/{*:tenant}/roles', [TenantRoles::class, 'create'])
            ->get('/tenants/{*:tenant}/roles', [TenantRoles::class, 'list'])
            ->get('/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'read'])
            ->patch('/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'update'])
            ->delete('/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'delete']);
    }

}