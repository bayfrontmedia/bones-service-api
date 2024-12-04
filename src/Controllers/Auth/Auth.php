<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Models\ApiModel;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
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
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\Totp;
use Bayfront\BonesService\Rbac\User;

class Auth extends AuthApiController
{

    /**
     * Authenticate email or abort.
     *
     * @param string $email
     * @param string $fail_event
     * @param bool $check_verified
     * @return User
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function authenticateEmail(string $email, string $fail_event, bool $check_verified = true): User
    {

        $authenticator = new EmailAuthenticator($this->rbacService);

        try {
            return $authenticator->authenticate($email, $check_verified);
        } catch (UserDoesNotExistException) {
            $this->events->doEvent($fail_event, $email);
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->events->doEvent($fail_event, $email);
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->events->doEvent($fail_event, $email);
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

    }

    /**
     * Create and save TFA.
     *
     * @param string $user_id
     * @param string $email
     * @param string $fail_event
     * @return Totp
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function createTfa(string $user_id, string $email, string $fail_event): Totp
    {

        /*
         * The user has already been authenticated.
         */

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {

            return $userMetaModel->createTotp(
                $user_id,
                $userMetaModel->totp_meta_key_tfa,
                $this->apiService->getConfig('auth.tfa.wait', 3),
                $this->apiService->getConfig('auth.tfa.duration', 15),
                $this->apiService->getConfig('auth.tfa.length', 6),
                $this->apiService->getConfig('auth.tfa.type', $this->rbacService::TOTP_TYPE_NUMERIC)
            );

        } catch (AlreadyExistsException) {
            $this->events->doEvent($fail_event, $email);
            $this->abort(409, 'Unable to create TFA: Wait time not yet elapsed');
        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unable to create TFA: Unexpected error', $e);
        }

    }

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
         * TOTP is deleted on successful TotpAuthenticator,
         * but for added security, they will be deleted
         * whenever a successful authentication has completed
         * and new tokens have been created.
         *
         * For added security, ensure all TOTP's are deleted.
         */

        $userMetaModel = new UserMetaModel($this->rbacService);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_password);

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

            $this->events->doEvent('api.auth.success', $user);
            $this->respond(201, $schema);

        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, $e->getMessage());
        }

    }

    /**
     * Login with email and create TFA.
     * On success, executes api.auth.tfa.request event and responds with 201 HTTP status code.
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

        $user = $this->authenticateEmail($body['email'], 'api.auth.fail.email');

        $totp = $this->createTfa($user->getId(), $user->getEmail(), 'api.auth.fail.email');

        $this->events->doEvent('api.auth.tfa.request', $user, $totp);
        $this->respond(201);

    }

    /**
     * Login with email and password.
     * Creates TOTP if needed.
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
            $this->events->doEvent('api.auth.fail.password', $body['email']);
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->events->doEvent('api.auth.fail.password', $body['email']);
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->events->doEvent('api.auth.fail.password', $body['email']);
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

        if ($this->apiService->getConfig('auth.tfa.enabled') === true) {

            $totp = $this->createTfa($user->getId(), $user->getEmail(), 'api.auth.fail.password');
            $this->events->doEvent('api.auth.tfa.request', $user, $totp);
            $this->respond(201);

        } else {
            $this->respondWithTokens($user);
        }

    }

    /**
     * Login.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function login(): void
    {

        if ($this->apiService->getConfig('auth.tfa.enabled') === true
            && $this->apiService->getConfig('auth.tfa.otp') === true) {
            $this->loginWithEmail();
        } else {
            $this->loginWithPassword();
        }

    }

    /**
     * Login with email and TFA.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function tfa(): void
    {

        if ($this->apiService->getConfig('auth.tfa.enabled') === false) {
            $this->abort(404, 'Not found');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'token' => 'required|isString'
        ]);

        $authenticator = new TotpAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email'], $body['token']);
        } catch (TotpDoesNotExistException|UserDoesNotExistException) {
            $this->events->doEvent('api.auth.fail.tfa', $body['email']);
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->events->doEvent('api.auth.fail.tfa', $body['email']);
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->events->doEvent('api.auth.fail.tfa', $body['email']);
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
            $this->events->doEvent('api.auth.fail.refresh');
            $this->abort(401, 'Invalid refresh token');
        } catch (UserDisabledException) {
            $this->events->doEvent('api.auth.fail.refresh');
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->events->doEvent('api.auth.fail.refresh');
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

        $this->respondWithTokens($user);

    }

    /**
     * Request password reset and respond with 201 HTTP status code.
     * Executes api.auth.password_request event.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function passwordRequest(): void
    {

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.auth.fail.password-request');

        // Create TOTP

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {

            $totp = $userMetaModel->createTotp(
                $user->getId(),
                $userMetaModel->totp_meta_key_password,
                $this->apiService->getConfig('password_request.wait', 3),
                $this->apiService->getConfig('password_request.duration', 15),
                $this->apiService->getConfig('password_request.length', 36),
                $this->apiService->getConfig('password_request.type', $this->rbacService::TOTP_TYPE_ALPHANUMERIC)
            );

        } catch (AlreadyExistsException) {
            $this->events->doEvent('api.auth.fail.password-request', $body['email']);
            $this->abort(409, 'Unable to create TFA: Wait time not yet elapsed');
        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unable to create TFA: Unexpected error', $e);
        }

        $this->events->doEvent('api.auth.password_request', $user, $totp);

        $this->respond(201);

    }

    /**
     * Reset password and respond with 200 HTTP status code.
     * Executes rbac.user.password.updated event.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function password(): void
    {

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'password' => 'required|isString|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.auth.fail.password');

        // Verify TOTP

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        } catch (DoesNotExistException) {
            $this->events->doEvent('api.auth.fail.password', $body['email']);
            $this->abort(401, 'Unable to reset password: Token does not exist');
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $body['token'])) {
            $this->events->doEvent('api.auth.fail.password', $body['email']);
            $this->abort(401, 'Unable to reset password: Invalid token');
        }

        // Delete TOTP

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_password);

        // Update user

        $usersModel = new UsersModel($this->rbacService);

        try {

            $usersModel->update($user->getId(), [
                'password' => $body['password']
            ]);

        } catch (OrmServiceException $e) {
            $this->abort(500, 'Unexpected error resetting password', $e);
        }

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        $userMetaModel->deleteAllTokens($user->getId());

        $this->respond();

    }

    /**
     * Request new user verification TOTP and respond with 201 HTTP status code.
     * Executes api.user.verification event.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function verificationRequest(): void
    {

        if ($this->apiService->getConfig('user_verification.enabled') === false) {
            $this->abort(404, 'Not found');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.auth.fail.verification-request', false);

        if ($user->isVerified()) {
            $this->events->doEvent('api.auth.fail.verification-request', $body['email']);
            $this->abort(401, 'Unable to create verification: User is already verified');
        }

        // Create TOTP

        $apiModel = new ApiModel($this->apiService);

        try {

            $apiModel->createUserVerificationRequest($user->getResource());

        } catch (AlreadyExistsException) {
            $this->events->doEvent('api.auth.fail.verification-request', $body['email']);
            $this->abort(409, 'Unable to create verification: Wait time not yet elapsed');
        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unable to create verification: Unexpected error', $e);
        }

        $this->respond(201);

    }

    /**
     * Verify new user verification TOTP.
     * Executes rbac.user.verification.verified event.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function verification(): void
    {

        if ($this->apiService->getConfig('user_verification.enabled') === false) {
            $this->abort(404, 'Not found');
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.auth.fail.verification', false);

        if ($user->isVerified()) {
            $this->events->doEvent('api.auth.fail.verification', $body['email']);
            $this->abort(401, 'Unable to verify user: User is already verified');
        }

        // Verify then delete TOTP

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_verification);
        } catch (DoesNotExistException) {
            $this->abort(401, 'Unable to verify user: Invalid verification token');
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $body['token'])) {
            $this->abort(401, 'Unable to verify user: Invalid verification token');
        }

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_verification);

        // Verify user

        $usersModel = new UsersModel($this->rbacService);

        $usersModel->verify($body['email']);

        $this->respond();

    }

}