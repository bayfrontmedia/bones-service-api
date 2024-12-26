<?php

namespace Bayfront\BonesService\Api\Models;

use Bayfront\Bones\Abstracts\Model;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\User;

class ApiModel extends Model
{

    protected ApiService $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        parent::__construct($apiService->events);
    }

    /**
     * Create user verification TOTP and execute
     * api.user.verification event.
     *
     * @param OrmResource $user
     * @return void
     * @throws AlreadyExistsException
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function createUserVerificationRequest(OrmResource $user): void
    {

        $userMetaModel = new UserMetaModel($this->apiService->rbacService);

        $totp = $userMetaModel->createTotp(
            $user->getPrimaryKey(),
            $userMetaModel->totp_meta_key_verification,
            $this->apiService->getConfig('user.verification.wait', 3),
            $this->apiService->getConfig('user.verification.duration', 1440),
            $this->apiService->getConfig('user.verification.length', 36),
            $this->apiService->getConfig('user.verification.type', $this->apiService->rbacService::TOTP_TYPE_ALPHANUMERIC)
        );

        $this->apiService->events->doEvent('api.user.verification_request', new User($this->apiService->rbacService, $user), $totp);

    }

}