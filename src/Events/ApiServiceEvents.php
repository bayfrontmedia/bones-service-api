<?php

namespace Bayfront\BonesService\Api\Events;

use Bayfront\Bones\Abstracts\EventSubscriber;
use Bayfront\Bones\Application\Services\Events\EventSubscription;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Constants;
use Bayfront\Bones\Exceptions\ConstantAlreadyDefinedException;
use Bayfront\Bones\Interfaces\EventSubscriberInterface;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Commands\ApiSeed;
use Bayfront\BonesService\Api\Controllers\Abstracts\ApiController;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\NotAcceptableException;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;
use Bayfront\BonesService\Api\Models\ApiModel;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\OrmResource;
use Bayfront\BonesService\Rbac\Models\TenantInvitationsModel;
use Bayfront\BonesService\Rbac\Models\UserKeysModel;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\CronScheduler\Cron;
use Bayfront\CronScheduler\LabelExistsException;
use Bayfront\CronScheduler\SyntaxException;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Bayfront\StringHelpers\Str;
use Symfony\Component\Console\Application;
use Throwable;

class ApiServiceEvents extends EventSubscriber implements EventSubscriberInterface
{

    protected ApiService $apiService;
    protected Cron $scheduler;

    public function __construct(ApiService $apiService, Cron $scheduler)
    {
        $this->apiService = $apiService;
        $this->scheduler = $scheduler;
    }

    /**
     * @inheritDoc
     */
    public function getSubscriptions(): array
    {
        return [
            new EventSubscription('rbac.user.created', [$this, 'createUserVerificationRequest'], 10),
            new EventSubscription('rbac.user.email.updated', [$this, 'recreateUserVerificationRequest'], 10),
            new EventSubscription('api.controller', [$this, 'checkRequiredHeaders'], 5),
            new EventSubscription('api.controller', [$this, 'checkHttps'], 5),
            new EventSubscription('api.controller', [$this, 'checkIpWhitelist'], 5),
            new EventSubscription('api.response', [$this, 'setRequiredHeaders'], 5),
            new EventSubscription('bones.exception', [$this, 'setStatusCode'], 5),
            new EventSubscription('app.cli', [$this, 'addConsoleCommands'], 10),
            new EventSubscription('app.cli', [$this, 'scheduleApiJobs'], 10),
            new EventSubscription('app.bootstrap', [$this, 'defineRequestId'], 5)
        ];
    }

    /**
     * Create user verification request if user verification is enabled.
     *
     * @param OrmResource $user
     * @return void
     * @throws AlreadyExistsException
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function createUserVerificationRequest(OrmResource $user): void
    {

        if ($this->apiService->getConfig('user.verification.enabled') === false) {
            return;
        }

        $apiModel = new ApiModel($this->apiService);
        $apiModel->createUserVerificationRequest($user);

    }

    /**
     * Unverify user and create new user verification request if user verification is enabled.
     *
     * @param OrmResource $user
     * @return void
     * @throws AlreadyExistsException
     * @throws DoesNotExistException
     * @throws UnexpectedException
     */
    public function recreateUserVerificationRequest(OrmResource $user): void
    {

        $usersModel = new UsersModel($this->apiService->rbacService);
        $usersModel->unverify($user->get('email', ''));

        $this->createUserVerificationRequest($user);

    }

    /**
     * Check for required request headers.
     *
     * @param ApiController $apiController
     * @return void
     * @throws BadRequestException
     */
    public function checkRequiredHeaders(ApiController $apiController): void
    {

        if ($apiController->check_required_headers === false) {
            return;
        }

        $required_headers = $this->apiService->getConfig('request.headers', []);

        foreach ($required_headers as $k => $v) {

            if (Request::getHeader($k) !== $v) {
                throw new BadRequestException('Required header missing or invalid: ' . $k);
            }

        }

    }

    /**
     * Check request is made over HTTPS.
     *
     * @param ApiController $apiController
     * @return void
     * @throws NotAcceptableException
     */
    public function checkHttps(ApiController $apiController): void
    {

        if ($apiController->check_https === false) {
            return;
        }

        if (!Request::isHttps() && in_array(App::environment(), $this->apiService->getConfig('request.https_env', []))) {
            throw new NotAcceptableException('All requests must be made over HTTPS');
        }

    }

    /**
     * Restrict access by IP.
     *
     * @param ApiController $apiController
     * @return void
     * @throws ForbiddenException
     */
    public function checkIpWhitelist(ApiController $apiController): void
    {

        if ($apiController->check_ip_whitelist === false) {
            return;
        }

        $whitelist = $this->apiService->getConfig('request.ip_whitelist', []);

        if (!empty($whitelist)) {

            $ip = Request::getIp();

            if (!in_array($ip, $whitelist)) {
                throw new ForbiddenException('IP not allowed: ' . $ip);
            }

        }

    }

    /**
     * Set required response headers.
     *
     * @param ApiController $apiController
     * @return void
     */
    public function setRequiredHeaders(ApiController $apiController): void
    {

        if ($apiController->set_required_headers === false) {
            return;
        }

        $apiController->response->setHeaders($this->apiService->getConfig('response.headers', []));
    }

    /**
     * Set HTTP status code for thrown exception.
     *
     * @param Response $response
     * @param Throwable $e
     * @return void
     * @throws InvalidStatusCodeException
     */
    public function setStatusCode(Response $response, Throwable $e): void
    {

        if ($e instanceof ApiExceptionInterface) {
            $response->setStatusCode($e->getHttpStatusCode());
        }

    }

    /**
     * @param Application $application
     * @return void
     */
    public function addConsoleCommands(Application $application): void
    {
        $application->add(new ApiSeed($this->apiService));
    }

    /**
     * Run scheduled API jobs.
     *
     * @return void
     * @throws LabelExistsException
     * @throws SyntaxException
     */
    public function scheduleApiJobs(): void
    {

        $this->scheduler->call('delete-expired-totps', function () {

            $userMetaModel = new UserMetaModel($this->apiService->rbacService);
            $userMetaModel->deleteExpiredTotps($userMetaModel->totp_meta_key_password);
            $userMetaModel->deleteExpiredTotps($userMetaModel->totp_meta_key_tfa);
            $userMetaModel->deleteExpiredTotps($userMetaModel->totp_meta_key_verification);

        })->everyMinutes(15);

        $this->scheduler->call('delete-expired-tokens', function () {

            $userMetaModel = new UserMetaModel($this->apiService->rbacService);
            $userMetaModel->deleteExpiredTokens();

        })->everyMinutes(15);

        $this->scheduler->call('delete-expired-invitations', function () {

            $tenantInvitationsModel = new TenantInvitationsModel($this->apiService->rbacService);
            $tenantInvitationsModel->pruneQuietly(time());

        })->everyHours(12);

        $this->scheduler->call('delete-expired-keys', function () {

            $userKeysModel = new UserKeysModel($this->apiService->rbacService);
            $userKeysModel->pruneQuietly(time());

        })->everyHours(12);

        if ($this->apiService->rbacService->getConfig('user.verification.require') === true
            && (int)$this->apiService->getConfig('user.unverified_expiration', 0) > 0) {

            $this->scheduler->call('delete-unverified-users', function () {

                $usersModel = new UsersModel($this->apiService->rbacService);
                $usersModel->deleteUnverified(time() - (int)$this->apiService->getConfig('unverified_expiration'));

            })->daily();

        }

    }

    /**
     * Define request ID.
     * This can be used to identify and trace a single request throughout the application lifecycle.
     *
     * @return void
     * @throws ConstantAlreadyDefinedException
     */
    public function defineRequestId(): void
    {
        if ($this->apiService->getConfig('request.id.enabled') === true && !Constants::isDefined('REQUEST_ID')) {
            Constants::define('REQUEST_ID', strtolower(Str::uid($this->apiService->getConfig('request.id.length', 8))));
        }
    }

}