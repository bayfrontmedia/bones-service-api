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
use Bayfront\BonesService\Api\Controllers\Private\TenantInvitations;
use Bayfront\BonesService\Api\Controllers\Private\TenantMeta;
use Bayfront\BonesService\Api\Controllers\Private\TenantRolePermissions;
use Bayfront\BonesService\Api\Controllers\Private\TenantRoles;
use Bayfront\BonesService\Api\Controllers\Private\Tenants;
use Bayfront\BonesService\Api\Controllers\Private\TenantTeams;
use Bayfront\BonesService\Api\Controllers\Private\TenantUserMeta;
use Bayfront\BonesService\Api\Controllers\Private\TenantUserRoles;
use Bayfront\BonesService\Api\Controllers\Private\TenantUsers;
use Bayfront\BonesService\Api\Controllers\Private\TenantUserTeams;
use Bayfront\BonesService\Api\Controllers\Private\UserKeys;
use Bayfront\BonesService\Api\Controllers\Private\UserMeta;
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
    public Response $response;
    public RbacService $rbacService;
    protected array $config;

    /**
     * The container will resolve any dependencies.
     * EventService is required by the abstract service.
     *
     * @param EventService $events
     * @param FilterService $filters
     * @param Response $response
     * @param Cron $scheduler
     * @param RbacService $rbacService
     * @param array $config
     * @throws ApiServiceException
     */

    public function __construct(EventService $events, FilterService $filters, Response $response, Cron $scheduler, RbacService $rbacService, array $config)
    {
        $this->events = $events;
        $this->filters = $filters;
        $this->response = $response;
        $this->rbacService = $rbacService;
        $this->config = $config;

        parent::__construct($events);

        if (!App::has('Bayfront\LeakyBucket\AdapterInterface')) {
            throw new ApiServiceException('Unable to start ApiService: Bayfront\LeakyBucket\AdapterInterface not found in container');
        }

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
            // Tenant invitations
            ->post('/tenants/{*:tenant}/invitations', [TenantInvitations::class, 'create'])
            ->get('/tenants/{*:tenant}/invitations', [TenantInvitations::class, 'list'])
            ->get('/tenants/{*:tenant}/invitations/{*:id}', [TenantInvitations::class, 'read'])
            ->delete('/tenants/{*:tenant}/invitations/{*:id}', [TenantInvitations::class, 'delete'])
            // Tenant meta
            ->post('/tenants/{*:tenant}/meta', [TenantMeta::class, 'create'])
            ->get('/tenants/{*:tenant}/meta', [TenantMeta::class, 'list'])
            ->get('/tenants/{*:tenant}/meta/{*:id}', [TenantMeta::class, 'read'])
            ->patch('/tenants/{*:tenant}/meta/{*:id}', [TenantMeta::class, 'update'])
            ->delete('/tenants/{*:tenant}/meta/{*:id}', [TenantMeta::class, 'delete'])
            // Tenant role permissions
            ->post('/tenants/{*:tenant}/roles/{*:role}/permissions', [TenantRolePermissions::class, 'create'])
            ->get('/tenants/{*:tenant}/roles/{*:role}/permissions', [TenantRolePermissions::class, 'list'])
            ->get('/tenants/{*:tenant}/roles/{*:role}/permissions/{*:id}', [TenantRolePermissions::class, 'read'])
            ->delete('/tenants/{*:tenant}/roles/{*:role}/permissions/{*:id}', [TenantRolePermissions::class, 'delete'])
            // Tenant roles
            ->post('/tenants/{*:tenant}/roles', [TenantRoles::class, 'create'])
            ->get('/tenants/{*:tenant}/roles', [TenantRoles::class, 'list'])
            ->get('/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'read'])
            ->patch('/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'update'])
            ->delete('/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'delete'])
            // Tenant users
            ->post('/tenants/{*:tenant}/users', [TenantUsers::class, 'create'])
            ->get('/tenants/{*:tenant}/users', [TenantUsers::class, 'list'])
            ->get('/tenants/{*:tenant}/users/{*:id}', [TenantUsers::class, 'read'])
            ->delete('/tenants/{*:tenant}/users/{*:id}', [TenantUsers::class, 'delete'])
            // Tenant user meta
            ->post('/tenants/{*:tenant}/users/{*:tenant_user}/meta', [TenantUserMeta::class, 'create'])
            ->get('/tenants/{*:tenant}/users/{*:tenant_user}/meta', [TenantUserMeta::class, 'list'])
            ->get('/tenants/{*:tenant}/users/{*:tenant_user}/meta/{*:id}', [TenantUserMeta::class, 'read'])
            ->patch('/tenants/{*:tenant}/users/{*:tenant_user}/meta/{*:id}', [TenantUserMeta::class, 'update'])
            ->delete('/tenants/{*:tenant}/users/{*:tenant_user}/meta/{*:id}', [TenantUserMeta::class, 'delete'])
            // Tenant user roles
            ->post('/tenants/{*:tenant}/users/{*:tenant_user}/roles', [TenantUserRoles::class, 'create'])
            ->get('/tenants/{*:tenant}/users/{*:tenant_user}/roles', [TenantUserRoles::class, 'list'])
            ->get('/tenants/{*:tenant}/users/{*:tenant_user}/roles/{*:id}', [TenantUserRoles::class, 'read'])
            ->delete('/tenants/{*:tenant}/users/{*:tenant_user}/roles/{*:id}', [TenantUserRoles::class, 'delete'])
            // Tenant user teams
            ->post('/tenants/{*:tenant}/users/{*:tenant_user}/teams', [TenantUserTeams::class, 'create'])
            ->get('/tenants/{*:tenant}/users/{*:tenant_user}/teams', [TenantUserTeams::class, 'list'])
            ->get('/tenants/{*:tenant}/users/{*:tenant_user}/teams/{*:id}', [TenantUserTeams::class, 'read'])
            ->delete('/tenants/{*:tenant}/users/{*:tenant_user}/teams/{*:id}', [TenantUserTeams::class, 'delete'])
            // Tenants
            ->post('/tenants', [Tenants::class, 'create'])
            ->get('/tenants', [Tenants::class, 'list'])
            ->get('/tenants/{*:id}', [Tenants::class, 'read'])
            ->patch('/tenants/{*:id}', [Tenants::class, 'update'])
            ->delete('/tenants/{*:id}', [Tenants::class, 'delete'])
            // Tenant teams
            ->post('/tenants/{*:tenant}/teams', [TenantTeams::class, 'create'])
            ->get('/tenants/{*:tenant}/teams', [TenantTeams::class, 'list'])
            ->get('/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'read'])
            ->patch('/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'update'])
            ->delete('/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'delete'])
            // User keys
            ->post('/users/{*:user}/keys', [UserKeys::class, 'create'])
            ->get('/users/{*:user}/keys', [UserKeys::class, 'list'])
            ->get('/users/{*:user}/keys/{*:id}', [UserKeys::class, 'read'])
            ->patch('/users/{*:user}/keys/{*:id}', [UserKeys::class, 'update'])
            ->delete('/users/{*:user}/keys/{*:id}', [UserKeys::class, 'delete'])
            // User meta
            ->post('/users/{*:user}/meta', [UserMeta::class, 'create'])
            ->get('/users/{*:user}/meta', [UserMeta::class, 'list'])
            ->get('/users/{*:user}/meta/{*:id}', [UserMeta::class, 'read'])
            ->patch('/users/{*:user}/meta/{*:id}', [UserMeta::class, 'update'])
            ->delete('/users/{*:user}/meta/{*:id}', [UserMeta::class, 'delete'])
            // Users
            ->get('/users/logout', [Users::class, 'logout'])
            ->get('/users/me', [Users::class, 'me'])
            ->get('/users/me/invitations', [Users::class, 'listInvitations'])
            ->post('/users/me/invitations', [Users::class, 'acceptInvitation'])
            ->post('/users', [Users::class, 'create'])
            ->get('/users', [Users::class, 'list'])
            ->get('/users/{*:id}', [Users::class, 'read'])
            ->patch('/users/{*:id}', [Users::class, 'update'])
            ->delete('/users/{*:id}', [Users::class, 'delete']);

    }

}