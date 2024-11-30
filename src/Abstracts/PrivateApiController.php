<?php

namespace Bayfront\BonesService\Api\Abstracts;

use Bayfront\BonesService\Api\ApiService;
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

    protected User $user;

    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);
        $this->user = $this->authenticateUser();

        /*
         * TODO: Rate limit
         */

        $this->apiService->events->doEvent('api.controller.private', $this);

    }

    private function authenticateUser(): User
    {
        if (Request::hasHeader('Bearer')) {

            $authenticator = new TokenAuthenticator($this->apiService->rbacService);

            try {
                return $authenticator->authenticate(Request::getHeader('Bearer'), $authenticator::TOKEN_TYPE_ACCESS);
            } catch (InvalidTokenException|TokenDoesNotExistException|UserDoesNotExistException) {
                $this->apiService->throwException(403, 'Invalid credentials');
            } catch (UserDisabledException) {
                $this->apiService->throwException(403, 'User is disabled');
            } catch (UserNotVerifiedException) {
                $this->apiService->throwException(403, 'User is not verified');
            } catch (UnexpectedAuthenticationException $e) {
                $this->apiService->throwException(500, $e->getMessage());
            }

        } else if (Request::hasHeader('X-API-Key')) {

            $authenticator = new UserKeyAuthenticator($this->apiService->rbacService);

            try {
                return $authenticator->authenticate(Request::getHeader('X-API-Key'), Request::getIp(), Request::getReferer());
            } catch (InvalidUserKeyException|UserDoesNotExistException) {
                $this->apiService->throwException(403, 'Invalid credentials');
            } catch (ExpiredUserKeyException) {
                $this->apiService->throwException(403, 'API key is expired');
            } catch (InvalidDomainException) {
                $this->apiService->throwException(403, 'Domain not allowed');
            } catch (InvalidIpException) {
                $this->apiService->throwException(403, 'IP not allowed');
            } catch (UserDisabledException) {
                $this->apiService->throwException(403, 'User is disabled');
            } catch (UserNotVerifiedException) {
                $this->apiService->throwException(403, 'User is not verified');
            } catch (UnexpectedAuthenticationException $e) {
                $this->apiService->throwException(500, $e->getMessage());
            }

        }

        $this->apiService->throwException(403, 'Missing credentials');

    }

}