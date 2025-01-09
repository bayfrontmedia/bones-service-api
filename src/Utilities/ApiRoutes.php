<?php

namespace Bayfront\BonesService\Api\Utilities;

use Bayfront\BonesService\Api\Controllers\Auth\Auth;
use Bayfront\BonesService\Api\Controllers\Auth\User;
use Bayfront\BonesService\Api\Controllers\Private\Permissions;
use Bayfront\BonesService\Api\Controllers\Private\TenantInvitations;
use Bayfront\BonesService\Api\Controllers\Private\TenantMeta;
use Bayfront\BonesService\Api\Controllers\Private\TenantPermissions;
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
use Bayfront\RouteIt\Router;

class ApiRoutes
{

    /**
     * Define default API service routes.
     *
     * @param Router $router
     * @param string $route_prefix
     * @return void
     */
    public static function define(Router $router, string $route_prefix = ''): void
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
            // Tenants
            ->post($route_prefix . '/tenants', [Tenants::class, 'create'])
            ->get($route_prefix . '/tenants', [Tenants::class, 'list'])
            ->get($route_prefix . '/tenants/{*:id}', [Tenants::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:id}', [Tenants::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:id}', [Tenants::class, 'delete'])
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
            // Tenant permissions
            ->post($route_prefix . '/tenants/{*:tenant}/permissions', [TenantPermissions::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/permissions', [TenantPermissions::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/permissions/{*:id}', [TenantPermissions::class, 'read'])
            ->delete($route_prefix . '/tenants/{*:tenant}/permissions/{*:id}', [TenantPermissions::class, 'delete'])
            ->get($route_prefix . '/tenants/{*:tenant}/permissions/{*:id}/roles', [TenantPermissions::class, 'listRoles'])
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
            // Tenant teams
            ->post($route_prefix . '/tenants/{*:tenant}/teams', [TenantTeams::class, 'create'])
            ->get($route_prefix . '/tenants/{*:tenant}/teams', [TenantTeams::class, 'list'])
            ->get($route_prefix . '/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'read'])
            ->patch($route_prefix . '/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'update'])
            ->delete($route_prefix . '/tenants/{*:tenant}/teams/{*:id}', [TenantTeams::class, 'delete'])
            ->get($route_prefix . '/tenants/{*:tenant}/teams/{*:id}/users', [TenantTeams::class, 'listUsers'])
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
            // Users
            ->post($route_prefix . '/users/logout', [Users::class, 'logout'])
            ->get($route_prefix . '/users/me', [Users::class, 'me'])
            ->post($route_prefix . '/users', [Users::class, 'create'])
            ->get($route_prefix . '/users', [Users::class, 'list'])
            ->get($route_prefix . '/users/{*:id}', [Users::class, 'read'])
            ->patch($route_prefix . '/users/{*:id}', [Users::class, 'update'])
            ->delete($route_prefix . '/users/{*:id}', [Users::class, 'delete'])
            ->get($route_prefix . '/users/{*:id}/invitations', [Users::class, 'listInvitations'])
            ->post($route_prefix . '/users/{*:user}/invitations/{*:id}', [Users::class, 'acceptInvitation'])
            ->get($route_prefix . '/users/{*:id}/tenants', [Users::class, 'listTenants'])
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


            ;

    }

}