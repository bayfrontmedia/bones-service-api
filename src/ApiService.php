<?php

namespace Bayfront\BonesService\Api;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Service;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Api\Controllers\Auth;
use Bayfront\BonesService\Api\Controllers\Private\Permissions;
use Bayfront\BonesService\Api\Controllers\Public\Home;
use Bayfront\BonesService\Api\Events\ApiServiceEvents;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Filters\ApiServiceFilters;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\CronScheduler\Cron;
use Bayfront\HttpResponse\Response;
use Bayfront\RouteIt\Router;

class ApiService extends Service
{

    public EventService $events;
    public FilterService $filters;
    public RbacService $rbacService;
    public Response $response;
    protected array $config;

    /**
     * The container will resolve any dependencies.
     * EventService is required by the abstract service.
     *
     * @param EventService $events
     * @param FilterService $filters
     * @param RbacService $rbacService
     * @param Response $response
     * @param Cron $scheduler
     * @param array $config
     * @throws ApiServiceException
     */

    public function __construct(EventService $events, FilterService $filters, RbacService $rbacService, Response $response, Cron $scheduler, array $config)
    {
        $this->events = $events;
        $this->filters = $filters;
        $this->rbacService = $rbacService;
        $this->response = $response;
        $this->config = $config;

        parent::__construct($events);

        // Enqueue events

        try {
            $this->events->addSubscriptions(new ApiServiceEvents($this, $scheduler));
        } catch (ServiceException $e) {
            throw new ApiServiceException('Unable to start ApiService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        // Enqueue filters

        try {
            $this->filters->addSubscriptions(new ApiServiceFilters($this));
        } catch (ServiceException $e) {
            throw new ApiServiceException('Unable to start ApiService: ' . $e->getMessage(), $e->getCode(), $e->getPrevious());
        }

        $this->events->doEvent('api.start', $this);

    }

    /**
     * Get API configuration value in dot notation.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $key = '', mixed $default = null): mixed
    {
        return Arr::get($this->config, $key, $default);
    }

    /**
     * Add default API service routes.
     *
     * @param Router $router
     * @return void
     */
    public function addRoutes(Router $router): void
    {
        $router->get('/', [Home::class, 'index'])
            ->post('/auth/login', [Auth::class, 'login'])
            ->post('/auth/token', [Auth::class, 'token'])
            ->post('/permissions', [Permissions::class, 'create'])
            ->get('/permissions',  [Permissions::class, 'list'])
            ->get('/permissions/{*:id}',  [Permissions::class, 'read'])
            ->patch('/permissions/{*:id}',  [Permissions::class, 'update'])
            ->delete('/permissions/{*:id}',  [Permissions::class, 'delete']);
    }

}