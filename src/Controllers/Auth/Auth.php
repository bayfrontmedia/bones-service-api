<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
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
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\User;

class Auth extends AuthApiController
{

    /**
     * @param User $user
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function respondWithTokens(User $user): void
    {

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {

            $access_token = $userMetaModel->createToken($user->getId(), $userMetaModel::TOKEN_TYPE_ACCESS);
            $jwt = $userMetaModel->readToken($access_token);

            // Schema
            $schema = [
                'data' => [
                    'access' => $access_token,
                    'refresh' => $userMetaModel->createToken($user->getId(), $userMetaModel::TOKEN_TYPE_REFRESH),
                    'expires' => Arr::get($jwt, 'exp')
                ]
            ];

            $this->respond(201, $schema);

        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, $e->getMessage());
        }

    }

    /**
     * Authenticate with email and password.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function login(): void
    {

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'password' => 'required|isString|maxLength:255'
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
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function refresh(): void
    {

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'refresh_token' => 'required|isString'
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

    /**
     * Verify OTP.
     *
     * @return void
     */
    public function otpVerify(): void
    {

    }

    /**
     * Request password reset.
     *
     * @return void
     */
    public function passwordRequest(): void
    {

    }

    /**
     * Reset password.
     *
     * @return void
     */
    public function passwordReset(): void
    {

    }

}