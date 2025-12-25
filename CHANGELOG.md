# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

- `[Unreleased]` for upcoming features.
- `Added` for new features.
- `Changed` for changes in existing functionality.
- `Deprecated` for soon-to-be removed features.
- `Removed` for now removed features.
- `Fixed` for any bug fixes.
- `Security` in case of vulnerabilities

## [1.6.0] - Upcoming

### Added

- Added `getPostMeta` method to `ApiController`

### Fixed

- Bugfix in `getJsonBody` method of `ApiController`

## [1.5.0] - 2025.12.22

### Added

- Added additional Http exceptions

## [1.4.1] - 2025.12.09

### Changed

- Updated dependencies
- Updated Symfony console depreciated `add` function to `addCommand`

## [1.4.0] - 2025.11.06 

### Changed

- Updated model meta to not dot arrays
- Updated permissions to read `TenantPermissions`, `TenantRolePermissions` and to list permissions of `TenantUsers`
- Updated `Tenants` controller to ensure only the current tenant owner can update the owner if not an admin
- Updated dependencies
- Updated documentation

### Fixed

- Fixed bug in listing tenant permissions when not in tenant
- Fixed bug in `UsesResourceModel` where the query filter in the `listResources` method was not applied correctly to
  queries with user-defined filters

## [1.3.0] - 2025.06.06

### Added

- Added `CreatesOrUpdatesUser` trait
- Added newly added permissions in `ApiSeed` command
- Added `user.unverified.new_only` config value
- Added user impersonation and related event

### Changed

- Updated `Auth` controllers to use new `userTokensModel` in `bones-service-rbac` v1.3.0
- Updated dependencies

### Fixed

- Fixed bug where the `User->register()` method was not validating user meta
- Fixed bug in `ApiServiceEvents` not allowing `delete-unverified-users` job from running

## [1.2.0] - 2025.06.01

### Added

- Added `tenant_user_roles:update` permission
- Added `tenant_user_teams:update` permission

### Changed

- Updated dependencies
- Updated required permissions to `create` and `delete` from `TenantUserRoles`
- Updated required permissions to `create` and `delete` from `TenantUserTeams`

## [1.1.3] - 2025.05.27

### Changed

- Updated dependencies

## [1.1.2] - 2025.05.27

### Fixed

- Bugfix in `validateUserMeta` and `validateTenantMeta` methods for `Users` and `Tenants` controllers on update

## [1.1.1] - 2025.04.10

### Fixed

- Bugfix in `validateUserMeta` and `validateTenantMeta` methods for `Users` and `Tenants` controllers

## [1.1.0] - 2025.03.07

### Added

- Added `user` field to be returned in `AuthResource`
- Added `/users/me/tenants/{id}/permissions` endpoint
- Added meta upsert endpoints
- Added `/permissions/{id}/tenants` endpoint

### Changed

- Updated `/users/{id}/tenants` endpoint to return all tenants if user is admin
- Updated creation of user keys only when `identity.key` config value is `true`
- Updated `TenantUsers` controller `delete` method to allow self to remove from tenant
- Updated `TenantInvitations` controller `delete` method to allow self to delete invitation
- Updated unknown email address in `User` controller `passwordRequest` method to return `204` status
- Updated Postman assets

### Fixed

- Bugfix in `listTenants` method
- Bugfix in `TenantRolePermissions` controller `create` method where `tenant` is not needed by model
- Bugfix in `Users` controller
- Bugfix in `TenantUsers` controller `listPermissions` method not checking if tenant or user was enabled or if user was
  an admin

## [1.0.0] - 2025.01.09

### Added

- Initial release