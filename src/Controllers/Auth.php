<?php

namespace Bayfront\BonesService\Api\Controllers;

use Bayfront\BonesService\Api\Abstracts\ApiController;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Interfaces\ApiExceptionInterface;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Authenticators\PasswordAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\TokenAuthenticator;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidPasswordException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidTokenException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TokenDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserMeta;
use Bayfront\BonesService\Rbac\User;
use Bayfront\HttpRequest\Request;

class Auth extends ApiController
{

    public function __construct(ApiService $apiService)
    {
        parent::__construct($apiService);

        // Rate limit

        if ((int)$this->apiService->getConfig('rate_limit.auth', 0) > 0) {
            $this->enforceRateLimit(md5('auth-' . Request::getIp()), (int)$this->apiService->getConfig('rate_limit.auth'));
        }

    }

    /**
     * @param User $user
     * @return void
     * @throws ApiExceptionInterface
     */
    private function respondWithTokens(User $user): void
    {

        $userMeta = new UserMeta($this->rbacService);

        try {

            $this->respond(201, [
                'access' => $userMeta->createToken($user->getId(), $userMeta::TOKEN_TYPE_ACCESS),
                'refresh' => $userMeta->createToken($user->getId(), $userMeta::TOKEN_TYPE_REFRESH),
                'expires' => '123'
            ]);

        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, $e->getMessage());
        }

    }

    /**
     * Authenticate with email and password.
     *
     * @return void
     * @throws ApiExceptionInterface
     */
    public function login(): void
    {

        $body = $this->getBody([
            'email',
            'password'
        ], [
            'email',
            'password'
        ]);

        $authenticator = new PasswordAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email'], $body['password']);
        } catch (InvalidPasswordException|UserDoesNotExistException) {
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

        $this->respondWithTokens($user);

    }

    /**
     * Authenticate with refresh token.
     *
     * @return void
     * @throws ApiExceptionInterface
     */
    public function token(): void
    {

        $body = $this->getBody([
            'refresh_token'
        ], [
            'refresh_token'
        ]);

        $authenticator = new TokenAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['refresh_token'], $authenticator::TOKEN_TYPE_REFRESH);
        } catch (InvalidTokenException|TokenDoesNotExistException|UserDoesNotExistException) {
            $this->abort(401, 'Invalid refresh token');
        } catch (UserDisabledException) {
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

        $this->respondWithTokens($user);

    }

}