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

## [2.3.0] - UPCOMING

### Changed

- Updated `getResourceBody` method to include option for disallowed fields

### Fixed

- Fixed bug in `getResourceBody` checking fields do not exist

## [2.2.1] - 2024.12.29

### Fixed

- `X-Api-Key` header bugfix

## [2.2.0] - 2024.12.29

### Added

- Added Postman collection and environment assets
- Added `validateInActiveTenant` method in `ApiController`

### Changed

- Updated initial user created with `api:seed` to be automatically verified

### Fixed

- `/users/logout` route bugfix
- Fixed bug in `TenantUsers` controller not listing permissions for tenant owner
- Fixed bug in tenant-scoped controller `list` methods not returning `ForbiddenException` if tenant is not enabled 

## [2.1.1] - 2024.12.26

### Fixed

- Bugfix in `api.user.verification_request` event

## [2.1.0] - 2024.12.26

### Removed

- Removed `ApiServiceDevEvents`

## [2.0.2] - 2024.12.26

### Changed

- Moved `REQUEST_ID` constant to be defined in the `app.bootstrap` event

## [2.0.1] - 2024.12.23

### Added

- Tested up to PHP v8.4

## [2.0.0] - 2024.12.23

### Added

- Added `bones-service-rbac` functionality

### Changed

- Updated to work with Bones `v5.3`
- Updated all dependencies

## [1.1.0] - 2024.09.04

### Changed

- Updated to work with Bones `v5.0`
- Updated GitHub issue templates
- Updated all dependencies

## [1.0.1] - 2024.04.12

### Fixed

- Bugfix in `getBody` method

## [1.0.0] - 2024.04.06

### Added

- Initial release.