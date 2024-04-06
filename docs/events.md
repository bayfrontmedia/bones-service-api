# API service > Events

The following [events](https://github.com/bayfrontmedia/bones/blob/master/docs/services/events.md) are added by this service:

- `api.start`: Executes in the `ApiService` constructor as the first event available to this service. The `ApiService` instance is passed as a parameter.
- `api.controller`: Executes when any `ApiController` is instantiated. The controller is passed as a parameter.
- `api.controller.public`: Executes when an `ApiController` is not private. The controller is passed as a parameter.
- `api.controller.private`: Executes when an `ApiController` is private. The controller is passed as a parameter.
- `api.response`: Executes just before the API response is sent with the [respond](apiservice-class.md#respond) method. The `Bayfront\HttpResponse\Response` class is passed as a parameter.