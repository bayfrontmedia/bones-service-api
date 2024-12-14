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
     * @param string $route_prefix
     * @return void
     */
    public function addRoutes(Router $router, string $route_prefix = ''): void
    {

        $route_prefix = '/' . trim($route_prefix, '/');

        $router
            // Server
            ->get($route_prefix . '/server/status', [Server::class, 'status'])
            // Auth
            ->post($route_prefix . '/auth/login', [Auth::class, 'login'])
            ->post($route_prefix . '/auth/otp', [Auth::class, 'createOtp'])
            ->post($route_prefix . '/auth/tfa', [Auth::class, 'verifyTfa'])
            ->post($route_prefix . '/auth/refresh', [Auth::class, 'refresh'])
            // User
            ->post($route_prefix . '/user/register', [User::class, 'register'])
            ->post($route_prefix . '/user/password-request', [User::class, 'passwordRequest'])
            ->post($route_prefix . '/user/password', [User::class, 'resetPassword'])
            ->post($route_prefix . '/user/verification-request', [User::class, 'verificationRequest'])
            ->post($route_prefix . '/user/verify', [User::class, 'verify'])
            // Permissions
            ->post($route_prefix . '/permissions', [Permissions::class, 'create'])
            ->get($route_prefix . '/permissions', [Permissions::class, 'list'])
            ->get($route_prefix . '/permissions/{*:id}', [Permissions::class, 'read'])
            ->patch($route_prefix . '/permissions/{*:id}', [Permissions::class, 'update'])
            ->delete($route_prefix . '/permissions/{*:id}', [Permissions::class, 'delete'])
            ->get($route_prefix . '/permissions/{*:id}/roles', [Permissions::class, 'listRoles'])
            // Tenant invitations
            ->post($route_prefix . '/tenants/{*:tenant}/invitations', [TenantInvitations::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/invitations', [TenantInvitations::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/invitations/{*:id}', [TenantInvitations::class, 'read'])
            ->delete($route_prefix . '/tenants/{*:tenant}/invitations/{*:id}', [TenantInvitations::class, 'delete'])
            // Tenant meta
            ->post($route_prefix . '/tenants/{*:tenant}/meta', [TenantMeta::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/meta', [TenantMeta::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/meta/{*:id}', [TenantMeta::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:tenant}/meta/{*:id}', [TenantMeta::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:tenant}/meta/{*:id}', [TenantMeta::class, 'delete'])
            // Tenant role permissions
            ->post($route_prefix . '/tenants/{*:tenant}/roles/{*:role}/permissions', [TenantRolePermissions::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/roles/{*:role}/permissions', [TenantRolePermissions::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/roles/{*:role}/permissions/{*:id}', [TenantRolePermissions::class, 'read'])
            ->delete($route_prefix . '/tenants/{*:tenant}/roles/{*:role}/permissions/{*:id}', [TenantRolePermissions::class, 'delete'])
            // Tenant roles
            ->post($route_prefix . '/tenants/{*:tenant}/roles', [TenantRoles::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/roles', [TenantRoles::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:tenant}/roles/{*:id}', [TenantRoles::class, 'delete'])
            ->get($route_prefix . '/tenants/{*:tenant}/roles/{*:id}/users', [TenantRoles::class, 'listUsers'])
            // Tenant users
            ->post($route_prefix . '/tenants/{*:tenant}/users', [TenantUsers::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/users', [TenantUsers::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:id}', [TenantUsers::class, 'read'])
            ->delete($route_prefix . '/tenants/{*:tenant}/users/{*:id}', [TenantUsers::class, 'delete'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:id}/permissions', [TenantUsers::class, 'listPermissions'])
            // Tenant user meta
            ->post($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/meta', [TenantUserMeta::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/meta', [TenantUserMeta::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/meta/{*:id}', [TenantUserMeta::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/meta/{*:id}', [TenantUserMeta::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/meta/{*:id}', [TenantUserMeta::class, 'delete'])
            // Tenant user roles
            ->post($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/roles', [TenantUserRoles::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/roles', [TenantUserRoles::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/roles/{*:id}', [TenantUserRoles::class, 'read'])
            ->delete($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/roles/{*:id}', [TenantUserRoles::class, 'delete'])
            // Tenant user teams
            ->post($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/teams', [TenantUserTeams::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/teams', [TenantUserTeams::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/teams/{*:id}', [TenantUserTeams::class, 'read'])
            ->delete($route_prefix . '/tenants/{*:tenant}/users/{*:tenant_user}/teams/{*:id}', [TenantUserTeams::class, 'delete'])
            // Tenants
            ->post($route_prefix . '/tenants', [Tenants::class, 'create'])
            ->get($route_prefix . '/tenants', [Tenants::class, 'list'])
            ->get($route_prefix . '/tenants/{*:id}', [Tenants::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:id}', [Tenants::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:id}', [Tenants::class, 'delete'])
            // Tenant teams
            ->post($route_prefix . '/tenants/{*:tenant}/teams', [TenantTeams::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/teams', [TenantTeams::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'delete'])
            ->get($route_prefix . '/tenants/{*:tenant}/teams/{*:id}/users', [TenantTeams::class, 'listUsers'])
            // User keys
            ->post($route_prefix . '/users/{*:user}/keys', [UserKeys::class, 'create'])
            ->get($route_prefix . '/users/{*:user}/keys', [UserKeys::class, 'list'])
            ->get($route_prefix . '/users/{*:user}/keys/{*:id}', [UserKeys::class, 'read'])
            ->patch($route_prefix . '/users/{*:user}/keys/{*:id}', [UserKeys::class, 'update'])
            ->delete($route_prefix . '/users/{*:user}/keys/{*:id}', [UserKeys::class, 'delete'])
            // User meta
            ->post($route_prefix . '/users/{*:user}/meta', [UserMeta::class, 'create'])
            ->get($route_prefix . '/users/{*:user}/meta', [UserMeta::class, 'list'])
            ->get($route_prefix . '/users/{*:user}/meta/{*:id}', [UserMeta::class, 'read'])
            ->patch($route_prefix . '/users/{*:user}/meta/{*:id}', [UserMeta::class, 'update'])
            ->delete($route_prefix . '/users/{*:user}/meta/{*:id}', [UserMeta::class, 'delete'])
            // Users
            ->get($route_prefix . '/users/logout', [Users::class, 'logout'])
            ->get($route_prefix . '/users/me', [Users::class, 'me'])
            ->post($route_prefix . '/users', [Users::class, 'create'])
            ->get($route_prefix . '/users', [Users::class, 'list'])
            ->get($route_prefix . '/users/{*:id}', [Users::class, 'read'])
            ->patch($route_prefix . '/users/{*:id}', [Users::class, 'update'])
            ->delete($route_prefix . '/users/{*:id}', [Users::class, 'delete'])
            ->get($route_prefix . '/users/{*:id}/invitations', [Users::class, 'listInvitations'])
            ->post($route_prefix . '/users/{*:user}/invitations/{*:id}', [Users::class, 'acceptInvitation'])
            ->get($route_prefix . '/users/{*:id}/tenants', [Users::class, 'listTenants'])
        ;

    }

}