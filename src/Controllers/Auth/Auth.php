<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Controllers\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Schemas\AuthResource;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Authenticators\EmailAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\PasswordAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\TokenAuthenticator;
use Bayfront\BonesService\Rbac\Authenticators\TotpAuthenticator;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidPasswordException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\InvalidTokenException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TokenDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\TotpDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\User;

class Auth extends AuthApiController
{

    /**
     * Respond with access and refresh tokens.
     *
     * On success, executes api.auth.success event and responds with 201 HTTP status code.
     *
     * @param User $user
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function respondWithTokens(User $user): void
    {

        /*
         * For security purposes, delete all TOTP's once
         * the user successfully authenticates.
         */

        $userMetaModel = new UserMetaModel($this->rbacService);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);

        try {

            $access_token = $userMetaModel->createToken($user->getId(), $userMetaModel::TOKEN_TYPE_ACCESS);
            $jwt = $userMetaModel->readToken($access_token);

            $this->events->doEvent('api.auth.success', $user);

            $this->respond(201, AuthResource::create([
                'access' => $access_token,
                'refresh' => $userMetaModel->createToken($user->getId(), $userMetaModel::TOKEN_TYPE_REFRESH),
                'expires' => Arr::get($jwt, 'exp')
            ]));

        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unexpected error', 0, $e);
        }

    }

    /**
     * Authenticate with email + password.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function login(): void
    {

        if ($this->apiService->getConfig('auth.password.enabled') !== true) {
            $this->abort(404);
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255',
            'password' => 'required|isString|maxLength:255'
        ]);

        $authenticator = new PasswordAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email'], $body['password']);
        } catch (UserDoesNotExistException|InvalidPasswordException|UserDisabledException|UserNotVerifiedException) {
            $this->events->doEvent('api.auth.password.fail', $body['email']);
            $this->abort(401);
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, 'Unexpected error', 0, $e);
        }

        if ($this->apiService->getConfig('auth.password.tfa.enabled') === true) {

            $userMetaModel = new UserMetaModel($this->rbacService);

            try {

                $totp = $userMetaModel->createTotp(
                    $user->getId(),
                    $userMetaModel->totp_meta_key_tfa,
                    $this->apiService->getConfig('auth.password.tfa.wait', 3),
                    $this->apiService->getConfig('auth.password.tfa.duration', 15),
                    $this->apiService->getConfig('auth.password.tfa.length', 6),
                    $this->apiService->getConfig('auth.password.tfa.type', $this->rbacService::TOTP_TYPE_NUMERIC)
                );

            } catch (AlreadyExistsException) { // TFA exists and wait time has not elapsed
                $this->events->doEvent('api.auth.password.fail', $body['email']);
                $this->abort(429);
            } catch (DoesNotExistException|UnexpectedException $e) {
                $this->abort(500, 'Unexpected error', 0, $e);
            }

            $this->events->doEvent('api.auth.password.tfa', $user, $totp);
            $this->respond(204);

        } else {
            $this->respondWithTokens($user);
        }

    }

    /**
     * Initiate authentication by creating OTP.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function otp(): void
    {

        if ($this->apiService->getConfig('auth.otp.enabled') !== true) {
            $this->abort(404);
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $authenticator = new EmailAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email']);
        } catch (UserDoesNotExistException|UserDisabledException|UserNotVerifiedException) {
            $this->events->doEvent('api.auth.otp.fail', $body['email']);
            $this->abort(401);
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, 'Unexpected error', 0, $e);
        }

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {

            $totp = $userMetaModel->createTotp(
                $user->getId(),
                $userMetaModel->totp_meta_key_tfa,
                $this->apiService->getConfig('auth.otp.wait', 3),
                $this->apiService->getConfig('auth.otp.duration', 15),
                $this->apiService->getConfig('auth.otp.length', 6),
                $this->apiService->getConfig('auth.otp.type', $this->rbacService::TOTP_TYPE_NUMERIC)
            );

        } catch (AlreadyExistsException) { // OTP exists and wait time has not elapsed
            $this->events->doEvent('api.auth.otp.fail', $body['email']);
            $this->abort(429);
        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unexpected error', 0, $e);
        }

        $this->events->doEvent('api.auth.otp', $user, $totp);
        $this->respond(204);

    }

    /**
     * Authenticate by verifying TFA token.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function tfa(): void
    {

        if ($this->apiService->getConfig('auth.password.tfa.enabled') === false
            && $this->apiService->getConfig('auth.otp.enabled') === false) {
            $this->abort(404);
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255',
            'token' => 'required|isString'
        ]);

        $authenticator = new TotpAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email'], $body['token']);
        } catch (TotpDoesNotExistException|UserDoesNotExistException|UserDisabledException|UserNotVerifiedException) {
            $this->events->doEvent('api.auth.tfa.fail', $body['email']);
            $this->abort(401);
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, 'Unexpected error', 0, $e);
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

        if ($this->apiService->getConfig('auth.refresh.enabled') !== true) {
            $this->abort(404);
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'refresh_token' => 'required|isString'
        ]);

        $authenticator = new TokenAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['refresh_token'], $authenticator::TOKEN_TYPE_REFRESH);
        } catch (InvalidTokenException|TokenDoesNotExistException|UserDoesNotExistException|UserDisabledException|UserNotVerifiedException) {
            $this->events->doEvent('api.auth.refresh.fail');
            $this->abort(401);
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, 'Unexpected error', 0, $e);
        }

        $this->respondWithTokens($user);

    }

}