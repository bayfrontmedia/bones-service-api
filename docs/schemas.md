# [API service](README.md) > Schemas

The following schemas are added by this service:

- [AggregateObject](#aggregateobject)
- [AuthResource](#authresource)
- [CursorPaginationObject](#cursorpaginationobject)
- [ErrorResource](#errorresource)
- [PagePaginationObject](#pagepaginationobject)
- [PermissionCollection](#permissioncollection)
- [PermissionObject](#permissionobject)
- [PermissionResource](#permissionresource)
- [ServerStatusResource](#serverstatusresource)
- [TenantCollection](#tenantcollection)
- [TenantInvitationCollection](#tenantinvitationcollection)
- [TenantInvitationObject](#tenantinvitationobject)
- [TenantInvitationResource](#tenantinvitationresource)
- [TenantMetaCollection](#tenantmetacollection)
- [TenantMetaObject](#tenantmetaobject)
- [TenantMetaResource](#tenantmetaresource)
- [TenantObject](#tenantobject)
- [TenantResource](#tenantresource)
- [TenantRoleCollection](#tenantrolecollection)
- [TenantRoleObject](#tenantroleobject)
- [TenantRolePermissionCollection](#tenantrolepermissioncollection)
- [TenantRolePermissionObject](#tenantrolepermissionobject)
- [TenantRolePermissionResource](#tenantrolepermissionresource)
- [TenantRoleResource](#tenantroleresource)
- [TenantTeamsCollection](#tenantteamscollection)
- [TenantTeamsObject](#tenantteamsobject)
- [TenantTeamsResource](#tenantteamsresource)
- [TenantUserCollection](#tenantusercollection)
- [TenantUserMetaCollection](#tenantusermetacollection)
- [TenantUserMetaObject](#tenantusermetaobject)
- [TenantUserMetaResource](#tenantusermetaresource)
- [TenantUserObject](#tenantuserobject)
- [TenantUserResource](#tenantuserresource)
- [TenantUserRoleCollection](#tenantuserrolecollection)
- [TenantUserRoleObject](#tenantuserroleobject)
- [TenantUserRoleResource](#tenantuserroleresource)
- [TenantUserTeamCollection](#tenantuserteamcollection)
- [TenantUserTeamObject](#tenantuserteamobject)
- [TenantUserTeamResource](#tenantuserteamresource)
- [UserCollection](#usercollection)
- [UserKeyCollection](#userkeycollection)
- [UserKeyObject](#userkeyobject)
- [UserKeyResource](#userkeyresource)
- [UserMetaCollection](#usermetacollection)
- [UserMetaObject](#usermetaobject)
- [UserMetaResource](#usermetaresource)
- [UserObject](#userobject)
- [UserResource](#userresource)

## AggregateObject

## AuthResource

```json
{
  "data": {
    "access": "STRING",
    "refresh": "STRING",
    "expires": "2025-01-01 12:00:00"
  }
}
```

| Field         | Type           | Description         |
|---------------|----------------|---------------------|
| `access`      | `string`       | Access token (JWT)  |
| `refresh`     | `string`       | Refresh token (JWT) |
| `expires`     | `string`       | Expiration datetime |

## CursorPaginationObject

## ErrorResource

```json
{
  "error": {
    "status": 404,
    "title": "Not Found",
    "message": "Not Found",
    "link": null,
    "code": 0,
    "request_id": null,
    "elapsed": "0.010",
    "time": "2025-01-01T12:00:00+00:00"
  }
}
```

| Field        | Type           | Description                   |
|--------------|----------------|-------------------------------|
| `status`     | `int`          | HTTP status code              |
| `title`      | `string`       | HTTP status code phrase       |
| `message`    | `string`       | Detailed error message        |
| `link`       | `string\|null` | Link pertaining to this error |
| `code`       | `int`          | Code pertaining to this error |
| `request_id` | `string\|null` | Unique request ID             |
| `elapsed`    | `string`       | Time elapsed (in seconds)     |
| `time`       | `string`       | ISO 8601 date of error        |

## PagePaginationObject

```json
{
  "results": {
    "current": "",
    "total": "",
    "from": "",
    "to": ""
  },
  "page": {
    "size": "",
    "current": "",
    "previous": "",
    "next": "",
    "total": ""
  },
  "cursor": {
    "first": "",
    "last": ""
  }
}
```

TODO

## PermissionCollection

Object with properties: 

- `data`: Array of [PermissionObjects](#permissionobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## PermissionObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "name": "tenant:update",
  "description": "Update tenant",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                |
|---------------|----------------|----------------------------|
| `id`          | `int`          | Resource ID                |
| `name`        | `string`       | Permission name (unique)   |
| `description` | `string`       | Permission description     |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |

## PermissionResource

Object with property `data` which contains a [PermissionObject](#permissionobject).

## ServerStatusResource

```json
{
  "data": {
    "status": "OK"
  }
}
```

| Field        | Type           | Description                   |
|--------------|----------------|-------------------------------|
| `status`     | `string`       | Current server status         |

## TenantCollection

Object with properties:

- `data`: Array of [TenantObjects](#tenantobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantInvitationCollection

Object with properties:

- `data`: Array of [TenantInvitationObjects](#tenantinvitationobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantInvitationObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "email": "user@example.com",
  "tenant": "0193d64f-f782-709d-afa7-b444af500242",
  "role": "0193d654-ec87-758f-878e-b0065b490ad9",
  "expires_at": "2025-01-01 12:00:00",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field        | Type           | Description                    |
|--------------|----------------|--------------------------------|
| `id`         | `string`       | Resource ID                    |
| `email`      | `string`       | Email address                  |
| `tenant`     | `string`       | Tenant (ID)                    |
| `role`       | `string`       | Tenant role (ID)               |
| `expires_at` | `string`       | Invitation expiration datetime |
| `created_at` | `string`       | Resource creation datetime     |
| `updated_at` | `string\|null` | Last updated datetime          |

## TenantInvitationResource

Object with property `data` which contains a [TenantInvitationObject](#tenantinvitationobject).

## TenantMetaCollection

Object with properties:

- `data`: Array of [TenantMetaObjects](#tenantmetaobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantMetaObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant": "0193d64f-f782-709d-afa7-b444af500242",
  "meta_key": "tenant_meta_key",
  "meta_value": "Tenant meta value",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field        | Type           | Description                 |
|--------------|----------------|-----------------------------|
| `id`         | `string`       | Resource ID                 |
| `tenant`     | `string`       | Tenant (ID)                 |
| `meta_key`   | `string`       | Meta key (unique to tenant) |
| `meta_value` | `string`       | Meta value                  |
| `created_at` | `string`       | Resource creation datetime  |
| `updated_at` | `string\|null` | Last updated datetime       |

## TenantMetaResource

Object with property `data` which contains a [TenantMetaObject](#tenantmetaobject).

## TenantObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "owner": "0193d64f-f782-709d-afa7-b444af500242",
  "domain": "example-tenant",
  "name": "Example tenant",
  "meta": null,
  "enabled": true,
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field        | Type           | Description                |
|--------------|----------------|----------------------------|
| `id`         | `string`       | Resource ID                |
| `owner`      | `string`       | Tenant owner (user ID)     |
| `domain`     | `string`       | Tenant domain (unique)     |
| `name`       | `string`       | Tenant name                |
| `meta`       | `object\|null` | Tenant meta                |
| `enabled`    | `bool`         | Tenant enabled status      |
| `created_at` | `string`       | Resource creation datetime |
| `updated_at` | `string\|null` | Last updated datetime      |

## TenantResource

Object with property `data` which contains a [TenantObject](#tenantobject).

## TenantRoleCollection

Object with properties:

- `data`: Array of [TenantRoleObjects](#tenantroleobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantRoleObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant": "0193d64f-f782-709d-afa7-b444af500242",
  "name": "Example tenant",
  "description": "",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                    |
|---------------|----------------|--------------------------------|
| `id`          | `string`       | Resource ID                    |
| `tenant`      | `string`       | Tenant ID                      |
| `name`        | `string`       | Tenant name (unique to tenant) |
| `description` | `string`       | Description                    |
| `created_at`  | `string`       | Resource creation datetime     |
| `updated_at`  | `string\|null` | Last updated datetime          |

## TenantRolePermissionCollection

Object with properties:

- `data`: Array of [TenantRolePermissionObjects](#tenantrolepermissionobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantRolePermissionObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "role": "0193d64f-f782-709d-afa7-b444af500242",
  "permission": "0193d65c-e9fa-7ac4-8cd0-46e97daaf95a",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field        | Type           | Description                |
|--------------|----------------|----------------------------|
| `id`         | `string`       | Resource ID                |
| `role`       | `string`       | Role ID                    |
| `permission` | `string`       | Permission ID              |
| `created_at` | `string`       | Resource creation datetime |
| `updated_at` | `string\|null` | Last updated datetime      |

## TenantRolePermissionResource

Object with property `data` which contains a [TenantRolePermissionObject](#tenantrolepermissionobject).

## TenantRoleResource

Object with property `data` which contains a [TenantRoleObject](#tenantroleobject).

## TenantTeamsCollection

## TenantTeamsCollection

## TenantTeamsObject

## TenantTeamsResource

## TenantUserCollection

## TenantUserMetaCollection

## TenantUserMetaObject

## TenantUserMetaResource

## TenantUserObject

## TenantUserResource

## TenantUserRoleCollection

## TenantUserRoleObject

## TenantUserRoleResource

## TenantUserTeamCollection

## TenantUserTeamObject

## TenantUserTeamResource

## UserCollection

## UserKeyCollection

## UserKeyObject

## UserKeyResource

## UserMetaCollection

## UserMetaObject

## UserMetaResource

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
| `id`          | `string`       | Resource ID                |
| `email`       | `string`       | Email address (unique)     |
| `meta`        | `object\|null` | User meta                  |
| `admin`       | `bool`         | User admin status          |
| `enabled`     | `bool`         | User enabled status        |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |
| `verified_at` | `string\|null` | User verified datetime     |

## UserResource

Object with property `data` which contains a [UserObject](#userobject).