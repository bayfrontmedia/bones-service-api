# [API service](README.md) > ApiService class

The `ApiService` class contains the following Bones services:

- [EventService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) as `$this->events`
- [FilterService](https://github.com/bayfrontmedia/bones/blob/master/docs/services/filters.md) as `$this->filters`
- [Response](https://github.com/bayfrontmedia/bones/blob/master/docs/services/response.md) as `$this->response`
- [Cron](https://github.com/bayfrontmedia/bones/blob/master/docs/services/scheduler.md) as `$this->scheduler`
- [RbacService](https://github.com/bayfrontmedia/bones-service-rbac) as `$this->rbacService`

The API [events](events.md) and [filters](filters.md) are added in its constructor.

Methods include:

- [getConfig](#getconfig)

## getConfig

**Description**

Get API configuration value in dot notation.

**Parameters**

- `$key = ''` (string): Key to return in dot notation
- `$default = null` (mixed): Default value to return if not existing

**Returns**

- (mixed)