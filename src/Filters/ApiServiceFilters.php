<?php

namespace Bayfront\BonesService\Api\Filters;

use Bayfront\Bones\Abstracts\FilterSubscriber;
use Bayfront\Bones\Application\Services\Filters\FilterSubscription;
use Bayfront\Bones\Application\Utilities\App;
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
            new FilterSubscription('api.response', [$this, 'addMetadata'], 10),
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
     * Add metadata to API response when in debug mode.
     *
     * @param array $data
     * @return array
     */
    public function addMetadata(array $data): array
    {

        if (App::isDebug()) {

            $data = array_merge($data, [
                'meta' => [
                    'bonesVersion' => App::getBonesVersion(),
                    'apiVersion' => $this->apiService->getConfig('version', ''),
                    'clientIp' => Request::getIp(),
                    'elapsed' => App::getElapsedTime()
                ]
            ]);

        }

        return $data;

    }

}