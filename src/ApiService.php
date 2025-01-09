<?php

namespace Bayfront\BonesService\Api;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Service;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Exceptions\ServiceException;
use Bayfront\BonesService\Api\Events\ApiServiceEvents;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Filters\ApiServiceFilters;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\CronScheduler\Cron;
use Bayfront\HttpResponse\Response;

class ApiService extends Service
{

    public EventService $events;
    public FilterService $filters;
    public Response $response;
    public RbacService $rbacService;
    protected array $config;

    /**
     * The container will resolve any dependencies.
     * EventService is required by the abstract service.
     *
     * @param EventService $events
     * @param FilterService $filters
     * @param Response $response
     * @param Cron $scheduler
     * @param RbacService $rbacService
     * @param array $config
     * @throws ApiServiceException
     */

    public function __construct(EventService $events, FilterService $filters, Response $response, Cron $scheduler, RbacService $rbacService, array $config)
    {
        $this->events = $events;
        $this->filters = $filters;
        $this->response = $response;
        $this->rbacService = $rbacService;
        $this->config = $config;

        parent::__construct($events);

        if (!App::has('Bayfront\LeakyBucket\AdapterInterface')) {
            throw new ApiServiceException('Unable to start ApiService: Bayfront\LeakyBucket\AdapterInterface not found in container');
        }

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

}