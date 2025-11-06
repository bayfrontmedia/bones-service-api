<?php

namespace Bayfront\BonesService\Api\Filters;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\FilterSubscriber;
use Bayfront\Bones\Application\Services\Filters\FilterSubscription;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Constants;
use Bayfront\Bones\Exceptions\UndefinedConstantException;
use Bayfront\Bones\Interfaces\FilterSubscriberInterface;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\HttpRequest\Request;

class ApiServiceFilters extends FilterSubscriber implements FilterSubscriberInterface
{

    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptions(): array
    {

        return [
            new FilterSubscription('about.bones', [$this, 'addApiVersion'], 10),
            new FilterSubscription('api.response', [$this, 'addMetadata'], 5)
        ];

    }

    /**
     * Add API version to the array returned by the php bones about:bones console command.
     *
     * @param array $arr
     * @return array
     */
    public function addApiVersion(array $arr): array
    {
        return array_merge($arr, [
            'API version' => $this->apiService->getConfig('version', '')
        ]);
    }

    /**
     * Add metadata to API response when requested in request query.
     * Filters meta using the api.response.meta filter.
     *
     * @param array $data
     * @return array
     * @throws UndefinedConstantException
     */
    public function addMetadata(array $data): array
    {

        $meta_field = $this->apiService->getConfig('request.meta.field', 'meta');

        if ($this->apiService->getConfig('request.meta.enabled') === true
            && in_array(App::environment(), $this->apiService->getConfig('request.meta.env', []))
            && Request::getQuery($meta_field) == 'true') {

            $data[$meta_field] = array_merge((array)Arr::get($data, $meta_field, []), $this->apiService->filters->doFilter('api.response.meta', [
                'version' => $this->apiService->getConfig('version', ''),
                'client_ip' => Request::getIp(),
                'request_id' => Constants::isDefined('REQUEST_ID') ? Constants::get('REQUEST_ID') : null,
                'elapsed' => App::getElapsedTime(),
                'time' => date('c')
            ]));

            $data[$meta_field] = $this->apiService->filters->doFilter('api.response.meta', $data[$meta_field]);

        }

        return $data;

    }

}