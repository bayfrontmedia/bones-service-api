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

## [1.1.0] - Upcoming

### Added

- Added `user` field to be returned in `AuthResource`
- Added `/users/me/tenants/{id}/permissions` endpoint
- Added meta upsert endpoints

### Changed

- Updated `/users/{id}/tenants` endpoint to return all tenants if user is admin
- Updated creation of user keys only when `identity.key` config value is `true`
- Updated `TenantUsers` controller `delete` method to allow self to remove from tenant

### Fixed

- Bugfix in `listTenants` method
- Bugfix in `TenantRolePermissions` controller `create` method where `tenant` is not needed by model
- Bugfix in `Users` controller

## [1.0.0] - 2025.01.09

### Added

- Initial release