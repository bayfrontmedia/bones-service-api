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

## [1.3.0] - Upcoming

### Created

- Created `CreatesOrUpdatesUser` trait

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
- Bugfix in `TenantUsers` controller `listPermissions` method not checking if tenant or user was enabled or if user was an admin

## [1.0.0] - 2025.01.09

### Added

- Initial release