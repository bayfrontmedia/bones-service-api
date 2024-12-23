# [API service](README.md) > Filters

The following [filters](https://github.com/bayfrontmedia/bones/blob/master/docs/services/filters.md) are added by this service:

- `api.response`: Filters the API response sent with the [respond](controllers/apicontroller.md#respond) method of an `ApiController`.
- `api.response.meta`: Filters the meta array returned when enabled and requested. (See [configuration array](setup.md#configuration))

Default meta keys:

- `version`: API version as defined in the [config value](setup.md#configuration)
- `client_ip`: Client IP address
- `request_id`: `REQUEST_ID` constant, if defined (see request.id [config value](setup.md#configuration))
- `elapsed`: Time elapsed (in seconds)
- `time`: ISO 8601 date