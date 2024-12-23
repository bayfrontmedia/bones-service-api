<?php

namespace Bayfront\BonesService\Api\Events;

use Bayfront\Bones\Abstracts\EventSubscriber;
use Bayfront\Bones\Application\Services\Events\EventSubscription;
use Bayfront\Bones\Interfaces\EventSubscriberInterface;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Totp;
use Bayfront\BonesService\Rbac\User;
use JetBrains\PhpStorm\NoReturn;

class ApiServiceDevEvents extends EventSubscriber implements EventSubscriberInterface
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
            new EventSubscription('api.auth.otp', [$this, 'otp'], 10),
            new EventSubscription('api.auth.password.tfa', [$this, 'tfa'], 10),
            new EventSubscription('api.user.password_request', [$this, 'passwordRequest'], 10),
            new EventSubscription('api.user.verification_request', [$this, 'verificationRequest'], 10),
            new EventSubscription('rbac.user.verified', [$this, 'userVerified'], 10),
            new EventSubscription('rbac.user.password.updated', [$this, 'passwordUpdated'], 10),
            new EventSubscription('rbac.tenant.invitation.created', [$this, 'invitationCreated'], 10),
            new EventSubscription('rbac.tenant.invitation.accepted', [$this, 'invitationAccepted'], 10)
        ];
    }

    /**
     * @param User $user
     * @param Totp $totp
     * @return void
     */
    #[NoReturn] public function otp(User $user, Totp $totp): void
    {

        $this->apiService->response->sendJson([
            'event' => 'api.auth.otp',
            'user' => $user->read(),
            'totp' => $totp->getTotp()
        ]);

        die;

    }

    /**
     * @param User $user
     * @param Totp $totp
     * @return void
     */
    #[NoReturn] public function tfa(User $user, Totp $totp): void
    {

        $this->apiService->response->sendJson([
            'event' => 'api.auth.password.tfa',
            'user' => $user->read(),
            'totp' => $totp->getTotp()
        ]);

        die;

    }

    /**
     * @param User $user
     * @param Totp $totp
     * @return void
     */
    #[NoReturn] public function passwordRequest(User $user, Totp $totp): void
    {

        $this->apiService->response->sendJson([
            'event' => 'api.user.password_request',
            'user' => $user->read(),
            'totp' => $totp->getTotp()
        ]);

        die;

    }

    /**
     * @param OrmResource $user
     * @param Totp $totp
     * @return void
     */
    #[NoReturn] public function verificationRequest(OrmResource $user, Totp $totp): void
    {

        $this->apiService->response->sendJson([
            'event' => 'api.user.verification_request',
            'user' => $user->read(),
            'totp' => $totp->getTotp()
        ]);

        die;

    }

    /**
     * @param string $email
     * @return void
     */
    #[NoReturn] public function userVerified(string $email): void
    {

        $this->apiService->response->sendJson([
            'event' => 'rbac.user.verified',
            'email' => $email
        ]);

        die;

    }

    /**
     * @param OrmResource $resource
     * @return void
     */
    #[NoReturn] public function passwordUpdated(OrmResource $resource): void
    {

        $this->apiService->response->sendJson([
            'event' => 'rbac.user.password.updated',
            'user' => $resource->read(),
        ]);

        die;

    }

    /**
     * @param OrmResource $tenantInvitation
     * @return void
     */
    #[NoReturn] public function invitationCreated(OrmResource $tenantInvitation): void
    {

        $this->apiService->response->sendJson([
            'event' => 'rbac.tenant.invitation.created',
            'invitation' => $tenantInvitation->read()
        ]);

        die;

    }

    /**
     * @param OrmResource $user
     * @param string $tenant_id
     * @return void
     */
    #[NoReturn] public function invitationAccepted(OrmResource $user, string $tenant_id): void
    {

        $this->apiService->response->sendJson([
            'event' => 'rbac.tenant.invitation.accepted',
            'user' => $user->read(),
            'tenant_id' => $tenant_id
        ]);

        die;

    }

}