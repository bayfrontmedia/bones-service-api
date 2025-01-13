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
- [TenantPermissionCollection](#tenantpermissioncollection)
- [TenantPermissionObject](#tenantpermissionobject)
- [TenantPermissionResource](#tenantpermissionresource)
- [TenantResource](#tenantresource)
- [TenantRoleCollection](#tenantrolecollection)
- [TenantRoleObject](#tenantroleobject)
- [TenantRolePermissionCollection](#tenantrolepermissioncollection)
- [TenantRolePermissionObject](#tenantrolepermissionobject)
- [TenantRolePermissionResource](#tenantrolepermissionresource)
- [TenantRoleResource](#tenantroleresource)
- [TenantTeamCollection](#tenantteamcollection)
- [TenantTeamObject](#tenantteamobject)
- [TenantTeamResource](#tenantteamresource)
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

The `AggregateObject` has properties named after resource fields whose values are an array of the requested
aggregate function for that field.

For more information, see [getAggregate](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/ormcollection.md#getaggregate).

## AuthResource

```json
{
  "data": {
    "user": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
    "access": "STRING",
    "refresh": "STRING",
    "expires": "2025-01-01 12:00:00"
  }
}
```

| Field     | Type     | Description         |
|-----------|----------|---------------------|
| `user`    | `string` | User ID             |
| `access`  | `string` | Access token (JWT)  |
| `refresh` | `string` | Refresh token (JWT) |
| `expires` | `string` | Expiration datetime |

## CursorPaginationObject

```json
{
  "results": {
    "current": 10
  },
  "cursor": {
    "first": "ENCODED_STRING",
    "last": "ENCODED_STRING"
  }
}
```

| Field             | Type     | Description                       |
|-------------------|----------|-----------------------------------|
| `results.current` | `int`    | Number of results returned        |
| `cursor.first`    | `string` | Cursor of first returned resource |
| `cursor.last`     | `string` | Cursor of last returned resource  |

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
    "current": 10,
    "total": 100,
    "from": 1,
    "to": 10
  },
  "page": {
    "size": 10,
    "current": 1,
    "previous": null,
    "next": 2,
    "total": 10
  }
}
```

| Field             | Type        | Description                       |
|-------------------|-------------|-----------------------------------|
| `results.current` | `int`       | Number of results returned        |
| `results.total`   | `int`       | Total number of results existing  |
| `results.from`    | `int\|null` | First returned result             |
| `results.to`      | `int\|null` | Last returned result              |
| `page.size`       | `int`       | Result limit                      |
| `page.current`    | `int`       | Current page number               |
| `page.previous`   | `int\|null` | Previous page number              |
| `page.next`       | `int\|null` | Next page number                  |
| `page.total`      | `int`       | Total number of pages             |

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
| `tenant`     | `string`       | Tenant ID                      |
| `role`       | `string`       | Tenant role ID                 |
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
| `tenant`     | `string`       | Tenant ID                   |
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

## TenantPermissionCollection

Object with properties:

- `data`: Array of [TenantPermissionObjects](#tenantpermissionobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantPermissionObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant": "0193d64f-f782-709d-afa7-b444af500242",
  "permission": "019426cc-98db-7bd1-94b3-996d55c12562",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                |
|---------------|----------------|----------------------------|
| `id`          | `string`       | Resource ID                |
| `tenant`      | `string`       | Tenant ID                  |
| `permission`  | `string`       | Permission ID              |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |

## TenantPermissionResource

Object with property `data` which contains a [TenantPermissionObject](#tenantpermissionobject).

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
  "tenant_permission": "0193d65c-e9fa-7ac4-8cd0-46e97daaf95a",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field               | Type           | Description                |
|---------------------|----------------|----------------------------|
| `id`                | `string`       | Resource ID                |
| `role`              | `string`       | Role ID                    |
| `tenant_permission` | `string`       | Tenant permission ID       |
| `created_at`        | `string`       | Resource creation datetime |
| `updated_at`        | `string\|null` | Last updated datetime      |

## TenantRolePermissionResource

Object with property `data` which contains a [TenantRolePermissionObject](#tenantrolepermissionobject).

## TenantRoleResource

Object with property `data` which contains a [TenantRoleObject](#tenantroleobject).

## TenantTeamCollection

Object with properties:

- `data`: Array of [TenantTeamObjects](#tenantteamobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantTeamObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant": "0193d64f-f782-709d-afa7-b444af500242",
  "name": "Team name",
  "description": "Team description",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                  |
|---------------|----------------|------------------------------|
| `id`          | `string`       | Resource ID                  |
| `tenant`      | `string`       | Tenant ID                    |
| `name`        | `string`       | Team name (unique to tenant) |
| `description` | `string`       | Team description             |
| `created_at`  | `string`       | Resource creation datetime   |
| `updated_at`  | `string\|null` | Last updated datetime        |

## TenantTeamResource

Object with property `data` which contains a [TenantTeamObject](#tenantteamobject).

## TenantUserCollection

Object with properties:

- `data`: Array of [TenantUserObjects](#tenantuserobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantUserMetaCollection

Object with properties:

- `data`: Array of [TenantUserMetaObjects](#tenantusermetaobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantUserMetaObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant_user": "0193d64f-f782-709d-afa7-b444af500242",
  "meta_key": "tenant_meta_key",
  "meta_value": "Tenant meta value",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                      |
|---------------|----------------|----------------------------------|
| `id`          | `string`       | Resource ID                      |
| `tenant_user` | `string`       | Tenant user ID                   |
| `meta_key`    | `string`       | Meta key (unique to tenant user) |
| `meta_value`  | `string`       | Meta value                       |
| `created_at`  | `string`       | Resource creation datetime       |
| `updated_at`  | `string\|null` | Last updated datetime            |

## TenantUserMetaResource

Object with property `data` which contains a [TenantUserMetaObject](#tenantusermetaobject).

## TenantUserObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant": "0193d64f-f782-709d-afa7-b444af500242",
  "user": "0193d669-1e76-70d5-88f5-17dcf797fc68",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                |
|---------------|----------------|----------------------------|
| `id`          | `string`       | Resource ID                |
| `tenant`      | `string`       | Tenant ID                  |
| `user`        | `string`       | User ID                    |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |

## TenantUserResource

Object with property `data` which contains a [TenantUserObject](#tenantuserobject).

## TenantUserRoleCollection

Object with properties:

- `data`: Array of [TenantUserRoleObjects](#tenantuserroleobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantUserRoleObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant_user": "0193d64f-f782-709d-afa7-b444af500242",
  "role": "0193d66b-0c89-72a8-aa67-73e12b72bbad",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                |
|---------------|----------------|----------------------------|
| `id`          | `string`       | Resource ID                |
| `tenant_user` | `string`       | Tenant user ID             |
| `role`        | `string`       | Tenant role ID             |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |

## TenantUserRoleResource

Object with property `data` which contains a [TenantUserRoleObject](#tenantuserroleobject).

## TenantUserTeamCollection

Object with properties:

- `data`: Array of [TenantUserTeamObjects](#tenantuserteamobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## TenantUserTeamObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "tenant_user": "0193d64f-f782-709d-afa7-b444af500242",
  "team": "0193d66b-0c89-72a8-aa67-73e12b72bbad",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field         | Type           | Description                |
|---------------|----------------|----------------------------|
| `id`          | `string`       | Resource ID                |
| `tenant_user` | `string`       | Tenant user ID             |
| `team`        | `string`       | Tenant team ID             |
| `created_at`  | `string`       | Resource creation datetime |
| `updated_at`  | `string\|null` | Last updated datetime      |

## TenantUserTeamResource

Object with property `data` which contains a [TenantUserTeamObject](#tenantuserteamobject).

## UserCollection

Object with properties:

- `data`: Array of [UserObjects](#userobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## UserKeyCollection

Object with properties:

- `data`: Array of [UserKeyObjects](#userkeyobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## UserKeyObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "user": "0193d64f-f782-709d-afa7-b444af500242",
  "name": "Descriptive key name",
  "key_value": "",
  "allowed_domains": null,
  "allowed_ips": null,
  "expires_at": "2025-01-01 12:00:00",
  "last_used": "2025-01-01 12:00:00",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field             | Type           | Description                                |
|-------------------|----------------|--------------------------------------------|
| `id`              | `string`       | Resource ID                                |
| `user`            | `string`       | User ID                                    |
| `name`            | `string`       | User key name                              |
| `key_value`       | `string`       | User key value (returned once on creation) |
| `allowed_domains` | `array\|null`  | Domain whitelist                           |
| `allowed_ips`     | `array\|null`  | IP whitelist                               |
| `expires_at`      | `string`       | User key expiration datetime               |
| `last_used`       | `string`       | User key last used datetime                |
| `created_at`      | `string`       | Resource creation datetime                 |
| `updated_at`      | `string\|null` | Last updated datetime                      |

## UserKeyResource

Object with property `data` which contains a [UserKeyObject](#userkeyobject).

## UserMetaCollection

Object with properties:

- `data`: Array of [UserMetaObjects](#usermetaobject)
- `aggregate`: Optional [AggregateObject](#aggregateobject)
- `pagination`: Optional [PagePaginationObject](#pagepaginationobject) or [CursorPaginationObject](#cursorpaginationobject)

## UserMetaObject

```json
{
  "id": "0193d628-a1bf-76a5-b02c-6697b50f94b7",
  "user": "0193d64f-f782-709d-afa7-b444af500242",
  "meta_key": "user_meta_key",
  "meta_value": "User meta value",
  "created_at": "2025-01-01 12:00:00",
  "updated_at": "2025-01-01 12:00:00"
}
```

| Field        | Type           | Description                |
|--------------|----------------|----------------------------|
| `id`         | `string`       | Resource ID                |
| `user`       | `string`       | User ID                    |
| `meta_key`   | `string`       | Meta key (unique to user)  |
| `meta_value` | `string`       | Meta value                 |
| `created_at` | `string`       | Resource creation datetime |
| `updated_at` | `string\|null` | Last updated datetime      |

## UserMetaResource

Object with property `data` which contains a [UserMetaObject](#usermetaobject).

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