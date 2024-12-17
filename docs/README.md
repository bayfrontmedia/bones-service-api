# API service

The API service gives full API functionality to the [Bones RBAC service](https://github.com/bayfrontmedia/bones-service-rbac).

- [Initial setup](setup.md)
- [ApiService](apiservice-class.md)
- [Exceptions](exceptions.md)
- [Controllers](controllers/README.md)
- [Events](events.md)
- [Filters](filters.md)
- Schemas

## General usage

Besides events and filters, the API service interaction happens within controllers.

Any controller used by the API service must extend `Bayfront\BonesService\Api\Abstracts\ApiController`,
which implements an `Bayfront\BonesService\Api\Interfaces\ApiControllerInterface`.

The interface requires only one method, `isPrivate`, which returns a boolean value.
its value determines which of the API controller events are executed.

The [ApiService class](apiservice-class.md) is available within the controller as `$this->apiService`.