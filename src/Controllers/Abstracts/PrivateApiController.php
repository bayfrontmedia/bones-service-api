<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Rbac\Authenticators\TokenAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\UserKeyAuthenticator;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\ExpiredUserKeyException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidDomainException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidIpException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidTokenException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidUserKeyException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TokenDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\User;
use Bayfront\Container\ContainerException;
use Bayfront\HttpRequest\Request;

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

    /**
     * Identify user using an enabled identification method,
     * and places User class into the container.
     *
     * @return User
     * @throws ApiServiceException
     * @throws ForbiddenException
     */
    private function identifyUser(): User
    {

        if ($this->apiService->getConfig('identity.token') === true && Request::hasHeader('Bearer')) {

            $authenticator = new TokenAuthenticator($this->rbacService);

            try {

                $user = $authenticator->authenticate(Request::getHeader('Bearer'), $authenticator::TOKEN_TYPE_ACCESS);

                $container = App::getContainer();
                $container->set($user::class, $user);
                $container->setAlias('user', $user::class);

                return $user;

            } catch (InvalidTokenException|TokenDoesNotExistException|UserDoesNotExistException) {
                throw new ForbiddenException('Invalid credentials');
            } catch (UserDisabledException) {
                throw new ForbiddenException('User is disabled');
            } catch (UserNotVerifiedException) {
                throw new ForbiddenException('User is not verified');
            } catch (UnexpectedAuthenticationException|ContainerException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        } else if ($this->apiService->getConfig('identity.key') === true && Request::hasHeader('X-Api-Key')) {

            $authenticator = new UserKeyAuthenticator($this->rbacService);

            if (is_string(Request::getReferer())) {
                $referer = Request::getReferer();
            } else {
                $referer = '';
            }

            try {

                $user = $authenticator->authenticate(Request::getHeader('X-Api-Key'), Request::getIp(), $referer);

                $container = App::getContainer();
                $container->set($user::class, $user);
                $container->setAlias('user', $user::class);

                return $user;

            } catch (InvalidUserKeyException|UserDoesNotExistException) {
                throw new ForbiddenException('Invalid credentials');
            } catch (ExpiredUserKeyException) {
                throw new ForbiddenException('API key is expired');
            } catch (InvalidDomainException) {
                throw new ForbiddenException('Domain not allowed');
            } catch (InvalidIpException) {
                throw new ForbiddenException('IP not allowed');
            } catch (UserDisabledException) {
                throw new ForbiddenException('User is disabled');
            } catch (UserNotVerifiedException) {
                throw new ForbiddenException('User is not verified');
            } catch (UnexpectedAuthenticationException|ContainerException $e) {
                throw new ApiServiceException($e->getMessage());
            }

        }

        throw new ForbiddenException('Missing credentials');

    }

}