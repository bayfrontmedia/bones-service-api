<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\BonesService\Api\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
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
use Bayfront\BonesService\Rbac\User;

class Auth extends AuthApiController
{

    /**
     * Create TOTP and respond with 201 HTTP status code.
     * Executes rbac.user.totp.created event.
     *
     * @param string $user_id
     * @param int $length
     * @param string $type
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function createTotp(string $user_id, int $length, string $type): void
    {

        /*
         * The user has already been authenticated.
         */


        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $userMetaModel->createUserTotp($user_id, $length, $type);
        } catch (AlreadyExistsException) {
            $this->abort(409, 'TOTP already exists: Wait time not yet elapsed');
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to create TOTP: Unexpected error', $e);
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

        /*
         * TOTP is deleted on successful TotpAuthenticator,
         * but for added security, they will be deleted
         * whenever a successful authentication has completed
         * and new tokens have been created.
         *
         * For added security, ensure all TOTP's are deleted.
         */

        $userMetaModel = new UserMetaModel($this->rbacService);
        $userMetaModel->deletePasswordRequest($user->getId());
        $userMetaModel->deleteUserTotp($user->getId());

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
     * @param string $email
     * @param bool $check_verified
     * @return User
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function authenticateEmail(string $email, bool $check_verified = true): User
    {

        $authenticator = new EmailAuthenticator($this->rbacService);

        try {
            return $authenticator->authenticate($email, $check_verified);
        } catch (UserDoesNotExistException) {
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

    }

    /**
     * Login with email and create TOTP.
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

        $user = $this->authenticateEmail($body['email']);

        $this->createTotp($user->getId(), $this->apiService->getConfig('auth.login.mfa.length', 6), $this->apiService->getConfig('auth.login.mfa.type', $this->rbacService::TOTP_TYPE_NUMERIC));

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
            $this->abort(401, 'Invalid credentials');
        } catch (UserDisabledException) {
            $this->abort(401, 'User is disabled');
        } catch (UserNotVerifiedException) {
            $this->abort(401, 'User is unverified');
        } catch (UnexpectedAuthenticationException $e) {
            $this->abort(500, $e->getMessage());
        }

        if ($this->apiService->getConfig('auth.login.mfa.enabled') === true) {

            $this->createTotp($user->getId(), $this->apiService->getConfig('auth.login.mfa.length', 6), $this->apiService->getConfig('auth.login.mfa.type', $this->rbacService::TOTP_TYPE_NUMERIC));

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
            && $this->apiService->getConfig('auth.login.mfa.totp') === true) {

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
     * Verify OTP.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function otpVerify(): void
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
            'token' => 'required|isString'
        ]);

        $authenticator = new TotpAuthenticator($this->rbacService);

        try {
            $user = $authenticator->authenticate($body['email'], $body['token']);
        } catch (TotpDoesNotExistException|UserDoesNotExistException) {
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
     * Request password reset and respond with 201 HTTP status code.
     * Executes rbac.user.password.request event.
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

        $user = $this->authenticateEmail($body['email']);

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $userMetaModel->createPasswordRequest($user->getId(), $this->rbacService->getConfig('password_request.length', 36), $this->rbacService->getConfig('password_request.type', $this->rbacService::TOTP_TYPE_ALPHANUMERIC));
        } catch (AlreadyExistsException) {
            $this->abort(409, 'Password request already exists: Wait time not yet elapsed');
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unexpected error creating password request', $e);
        }

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
    public function passwordReset(): void
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

        $user = $this->authenticateEmail($body['email']);

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getPasswordRequest($user->getId());
        } catch (DoesNotExistException) {
            $this->abort(401, 'Invalid password reset token');
        }

        if (!$this->apiService->rbacService->hashMatches($totp->getValue(), $body['token'])) {
            $this->abort(401, 'Invalid password reset token value');
        }

        $usersModel = new UsersModel($this->rbacService);

        try {

            $usersModel->update($user->getId(), [
                'password' => $body['password']
            ]);

        } catch (OrmServiceException $e) {
            $this->abort(500, 'Unexpected error resetting password', $e);
        }

        $userMetaModel->deletePasswordRequest($user->getId());
        $userMetaModel->deleteUserTotp($user->getId());
        $userMetaModel->deleteAllTokens($user->getId());

        $this->respond();

    }

    /**
     * Request new user verification TOTP and respond with 201 HTTP status code.
     * Executes rbac.user.verification.request event.
     *
     * TODO:
     * When a new user is created (onCreated), this event should be executed if validation is required.
     * This will require the length and type settings to either be hard coded
     * or added to the RBAC service config.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function verificationRequest(): void
    {

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $user = $this->authenticateEmail($body['email'], false);

        /*
         * TODO:
         * Check if verification is enabled and if user is already verified.
         * Can add isVerified method to User class.
         */

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $userMetaModel->createUserVerification($user->getId(), $this->apiService->getConfig('user_verification.length', 36), $this->apiService->getConfig('user_verification.type', $this->rbacService::TOTP_TYPE_ALPHANUMERIC));
        } catch (AlreadyExistsException) {
            $this->abort(409, 'User verification already exists: Wait time not yet elapsed');
        } catch (UnexpectedException $e) {
            $this->abort(500, 'Unable to create user verification: Unexpected error', $e);
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
    public function verificationVerify(): void
    {

        /*
         * TODO:
         * Check if user is enabled and not yet verified
         */

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], false);

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getUserVerification($user->getId());
        } catch (DoesNotExistException) {
            $this->abort(401, 'Invalid verification token');
        }

        if (!$this->apiService->rbacService->hashMatches($totp->getValue(), $body['token'])) {
            $this->abort(401, 'Invalid verification token value');
        }

        $usersModel = new UsersModel($this->rbacService);

        $usersModel->verify($body['email']);

        $userMetaModel->deleteUserVerification($user->getId());

        $this->respond();

    }

}