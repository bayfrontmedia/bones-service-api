<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Rbac\User;

abstract class PrivateApiController extends ApiController
{

    public User $user;

    /**
     * @param ApiService $apiService
     * @throws ApiServiceException
     * @throws ForbiddenException
     * @throws TooManyRequestsException
     */
    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);
        $this->user = $this->identifyUser();

        // Rate limit

        if ((int)$this->apiService->getConfig('rate_limit.private', 0) > 0) {
            $this->enforceRateLimit(md5('private-' . $this->user->getId()), (int)$this->apiService->getConfig('rate_limit.private'));
        }

        $this->events->doEvent('api.controller.private', $this);

    }



}