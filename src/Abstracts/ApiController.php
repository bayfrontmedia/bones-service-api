<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\Bones\Abstracts\Controller;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiControllerInterface;
use Bayfront\LeakyBucket\AdapterException;
use Bayfront\LeakyBucket\Bucket;
use Bayfront\LeakyBucket\BucketException;
use Exception;

abstract class ApiController extends Controller implements ApiControllerInterface
{

    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        parent::__construct($this->apiService->events); // Fires the bones.controller event
        $this->apiService->events->doEvent('api.controller', $this);
    }

    /**
     * Enforce rate limit and set X-RateLimit headers.
     * On error, aborts with 429 HTTP status.
     *
     * @param string $id
     * @param int $limit
     * @return void
     */
    protected function rateLimitOrAbort(string $id, int $limit): void
    {

        try {

            /** @var Bucket $bucket */
            $bucket = App::make('Bayfront\LeakyBucket\Bucket', [
                'id' => $id,
                'settings' => [
                    'capacity' => $limit,
                    'leak' => 1 // TODO: This is not calculating correctly
                ]
            ]);

        } catch (Exception $e) {
            $this->apiService->throwException(500, $e->getMessage());
        }

        try {
            $bucket->leak()->fill()->save();
        } catch (BucketException) {

            $wait = round($bucket->getSecondsUntilCapacity());

            $this->apiService->response->setHeaders([
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => floor($bucket->getCapacityRemaining()),
                'X-RateLimit-Reset' => round($bucket->getSecondsUntilEmpty()),
                'Retry-After' => $wait
            ]);

            $this->apiService->throwException(429, 'Rate limit exceeded. Try again in ' . $wait . ' seconds');

        } catch (AdapterException $e) {
            $this->apiService->throwException(500, $e->getMessage());
        }

        $this->apiService->response->setHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => floor($bucket->getCapacityRemaining()),
            'X-RateLimit-Reset' => round($bucket->getSecondsUntilEmpty())
        ]);

    }

}