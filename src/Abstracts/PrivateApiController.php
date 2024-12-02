<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;
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
use Bayfront\HttpRequest\Request;

abstract class PrivateApiController extends ApiController
{

    public User $user;

    /**
     * @param ApiService $apiService
     * @throws ApiExceptionInterface
     */
    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);
        $this->user = $this->authenticateUser();

        // Rate limit

        if ((int)$this->apiService->getConfig('rate_limit.private', 0) > 0) {
            $this->enforceRateLimit(md5('private-' . $this->user->getId()), (int)$this->apiService->getConfig('rate_limit.private'));
        }

        $this->events->doEvent('api.controller.private', $this);

    }

    /**
     * @return User
     * @throws ApiExceptionInterface
     */
    private function authenticateUser(): User
    {

        if ($this->apiService->getConfig('auth.token') === true && Request::hasHeader('Bearer')) {

            $authenticator = new TokenAuthenticator($this->rbacService);

            try {
                return $authenticator->authenticate(Request::getHeader('Bearer'), $authenticator::TOKEN_TYPE_ACCESS);
            } catch (InvalidTokenException|TokenDoesNotExistException|UserDoesNotExistException) {
                $this->abort(403, 'Invalid credentials');
            } catch (UserDisabledException) {
                $this->abort(403, 'User is disabled');
            } catch (UserNotVerifiedException) {
                $this->abort(403, 'User is not verified');
            } catch (UnexpectedAuthenticationException $e) {
                $this->abort(500, $e->getMessage());
            }

        } else if ($this->apiService->getConfig('auth.key') === true && Request::hasHeader('X-API-Key')) {

            $authenticator = new UserKeyAuthenticator($this->rbacService);

            if (is_string(Request::getReferer())) {
                $referer = Request::getReferer();
            } else {
                $referer = '';
            }

            try {
                return $authenticator->authenticate(Request::getHeader('X-API-Key'), Request::getIp(), $referer);
            } catch (InvalidUserKeyException|UserDoesNotExistException) {
                $this->abort(403, 'Invalid credentials');
            } catch (ExpiredUserKeyException) {
                $this->abort(403, 'API key is expired');
            } catch (InvalidDomainException) {
                $this->abort(403, 'Domain not allowed');
            } catch (InvalidIpException) {
                $this->abort(403, 'IP not allowed');
            } catch (UserDisabledException) {
                $this->abort(403, 'User is disabled');
            } catch (UserNotVerifiedException) {
                $this->abort(403, 'User is not verified');
            } catch (UnexpectedAuthenticationException $e) {
                $this->abort(500, $e->getMessage());
            }

        }

        $this->abort(403, 'Missing credentials');

    }

}