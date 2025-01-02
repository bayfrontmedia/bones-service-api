# [API service](../README.md) > Controllers

If using any of the API service controllers, the router cannot utilize the `class_namespace` [config key](https://github.com/bayfrontmedia/bones/blob/master/docs/services/router.md)
since the API controllers reside in a different namespace than the controllers used at the app-level (see [routes](../setup.md#routes)).

Every API controller response should use the [respond](apicontroller.md#respond) method, 
and every exception should extend `ApiHttpException` or `ApiServiceException` (see [exceptions](../exceptions.md)).
This allows the execution of the appropriate filters and events, and ensures a valid API schema is always returned.

All API controllers should extend one of:

- [ApiController](apicontroller.md)
- [AuthApiController](authapicontroller.md)
- [PrivateApiController](privateapicontroller.md)
- [PublicApiController](publicapicontroller.md)

API controllers can also use any of the available [controller traits](../traits/README.md).
In addition, controllers can implement [CrudControllerInterface](crudcontrollerinterface.md).

## Public controllers

- [Server](server.md)

## Auth controllers

- [Auth](auth.md)
- [User](user.md)

## Private controllers

- [Permissions](permissions.md)
- [TenantInvitations](tenantinvitations.md)
- [TenantMeta](tenantmeta.md)
- [TenantPermissions](tenantpermissions.md)
- [TenantRolePermissions](tenantrolepermissions.md)
- [TenantRoles](tenantroles.md)
- [Tenants](tenants.md)
- [TenantTeams](tenantteams.md)
- [TenantUserMeta](tenantusermeta.md)
- [TenantUserRoles](tenantuserroles.md)
- [TenantUsers](tenantusers.md)
- [TenantUserTeams](tenantuserteams.md)
- [UserKeys](userkeys.md)
- [UserMeta](usermeta.md)
- [Users](users.md)