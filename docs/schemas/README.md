# [API service](../README.md) > Schemas

The following schemas are added by this service:

- AggregateObject
- AuthResource
- CursorPaginationObject
- ErrorResource
- PagePaginationObject
- PermissionCollection
- PermissionObject
- PermissionResource
- ServerStatusResource
- TenantCollection
- TenantInvitationCollection
- TenantInvitationObject
- TenantInvitationResource
- TenantMetaCollection
- TenantMetaObject
- TenantMetaResource
- TenantObject
- TenantResource
- TenantRoleCollection
- TenantRoleObject
- TenantRolePermissionCollection
- TenantRolePermissionObject
- TenantRolePermissionResource
- TenantRoleResource
- TenantTeamsCollection
- TenantTeamsObject
- TenantTeamsResource
- TenantUserCollection
- TenantUserMetaCollection
- TenantUserMetaObject
- TenantUserMetaResource
- TenantUserObject
- TenantUserResource
- TenantUserRoleCollection
- TenantUserRoleObject
- TenantUserRoleResource
- TenantUserTeamCollection
- TenantUserTeamObject
- TenantUserReamResource
- UserCollection
- UserKeyCollection
- UserKeyObject
- UserKeyResource
- UserMetaCollection
- UserMetaObject
- UserMetaResource
- [UserObject](#userobject)
- [UserResource](#userresource)

## UserObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "email": "user@example.com",
  "meta": null,
  "admin": false,
  "enabled": true,
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00",
  "verified_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                |
|---------------|----------------|----------------------------|
| `id`          | `string`       | User ID                    |
| `email`       | `string`       | Email address (unique)     |
| `meta`        | `object\|null` | User meta                  |
| `admin`       | `bool`         | User admin status          |
| `enabled`     | `bool`         | User enabled status        |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |
| `verified_at` | `string\|null` | User verified datetime     |

## UserResource

Object with property `data` which contains a [UserObject](#userobject).