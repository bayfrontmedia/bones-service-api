<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Authenticators\EmailAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\MfaAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\PasswordAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\TokenAuthenticator;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidPasswordException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidTokenException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\MfaDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TokenDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\User;

class Auth extends AuthApiController
{

    /**
     * Create MFA and respond with 201 HTTP status code.
     * Executes rbac.user.mfa.created event.
     *
     * @param string $email
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function createMfa(string $email): void
    {

        $usersModel = new UsersModel($this->rbacService);

        try {
            $usersModel->createMfa($email, $this->apiService->getConfig('auth.login.mfa.length', 6));
        } catch (AlreadyExistsException) {
            $this->abort(409, 'MFA already exists: Wait time not yet elapsed');
        } catch (DoesNotExistException $e) {
            $this->abort(500, 'Unable to create MFA: Unexpected error', $e);
        }

        $this->respond(201);

    }

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
     * Login with email and create MFA.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function loginWithEmail(): void
    {

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $authenticator = new EmailAuthenticator($this->rbacService);

        try {
            $authenticator->authenticate($body['email']);
        } catch (UserDoesNotExistException) {
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

        $this->createMfa($body['email']);

    }

    /**
     * Login with email and password.
     * Creates MFA if needed.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function loginWithPassword(): void
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

        if ($this->apiService->getConfig('auth.login.mfa.enabled') === true) {

            $this->createMfa($body['email']);

        } else {
            $this->respondWithTokens($user);
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

        if ($this->apiService->getConfig('auth.login.mfa.enabled') === true
            && $this->apiService->getConfig('auth.login.mfa.otp') === true) {

            $this->loginWithEmail();

        } else {
            $this->loginWithPassword();
        }

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

        if ($this->apiService->getConfig('auth.refresh') !== true) {
            $this->abort(404, 'Not found');
        }

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
     * Verify MFA.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function mfaVerify(): void
    {

        if ($this->apiService->getConfig('auth.login.mfa.enabled') === false) {
            $this->abort(404, 'Not found');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'mfa' => 'required|isString'
        ]);

        $authenticator = new MfaAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email'], $body['mfa']);
        } catch (MfaDoesNotExistException|UserDoesNotExistException) {
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
     * Request password reset.
     *
     * @return void
     */
    public function passwordRequest(): void
    {

        // TODO: Send this as user meta- delete expired. doEvent. Need scheduled job to delete expired

    }

    /**
     * Reset password.
     *
     * @return void
     */
    public function passwordReset(): void
    {

        // TODO: Revoke all access and refresh tokens, delete meta, doEvent (rbac.user.password.updated) - use $users->update()

    }

}