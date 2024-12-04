<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\BonesService\Api\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Models\ApiModel;
use Bayfront\BonesService\Orm\Exceptions\AlreadyExistsException;
use Bayfront\BonesService\Orm\Exceptions\DoesNotExistException;
use Bayfront\BonesService\Orm\Exceptions\OrmServiceException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\Authenticators\EmailAuthenticator;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UnexpectedAuthenticationException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDisabledException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserDoesNotExistException;
use Bayfront\BonesService\Rbac\Exceptions\Authentication\UserNotVerifiedException;
use Bayfront\BonesService\Rbac\Models\UserMetaModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;
use Bayfront\BonesService\Rbac\User as RbacUser;

class User extends AuthApiController
{

    /**
     * Authenticate email or abort.
     *
     * @param string $email
     * @param string $fail_event
     * @param bool $check_verified
     * @return RbacUser
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    private function authenticateEmail(string $email, string $fail_event, bool $check_verified = true): RbacUser
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
     * Request password reset and respond with 201 HTTP status code.
     * Executes api.user.password_request event.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function passwordRequest(): void
    {

        if ($this->apiService->getConfig('user.password_request.enabled') !== true) {
            $this->abort(404);
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255'
        ]);


        $user = $this->authenticateEmail($body['email'], 'api.user.password_request.fail');

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {

            $totp = $userMetaModel->createTotp(
                $user->getId(),
                $userMetaModel->totp_meta_key_password,
                $this->apiService->getConfig('user.password_request.wait', 3),
                $this->apiService->getConfig('user.password_request.duration', 15),
                $this->apiService->getConfig('user.password_request.length', 36),
                $this->apiService->getConfig('user.password_request.type', $this->rbacService::TOTP_TYPE_ALPHANUMERIC)
            );

        } catch (AlreadyExistsException) {
            $this->events->doEvent('api.user.password_request.fail', $body['email']);
            $this->abort(409, 'Unable to create TFA: Wait time not yet elapsed');
        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unable to create TFA: Unexpected error', $e);
        }

        $this->events->doEvent('api.user.password_request', $user, $totp);
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

        if ($this->apiService->getConfig('user.password_request.enabled') !== true) {
            $this->abort(404);
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'password' => 'required|isString|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.user.password.fail');

        // Validate token

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        } catch (DoesNotExistException) {
            $this->events->doEvent('api.user.password.fail', $body['email']);
            $this->abort(401, 'Unable to reset password: Token does not exist');
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $body['token'])) {
            $this->events->doEvent('api.user.password.fail', $body['email']);
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

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);
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

        if ($this->apiService->getConfig('user.verification.enabled') === false) {
            $this->abort(404);
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.user.verification_request.fail', false);

        if ($user->isVerified()) {
            $this->events->doEvent('api.user.verification_request.fail', $body['email']);
            $this->abort(401, 'Unable to create verification: User is already verified');
        }

        $apiModel = new ApiModel($this->apiService);

        try {

            $apiModel->createUserVerificationRequest($user->getResource());

        } catch (AlreadyExistsException) {
            $this->events->doEvent('api.user.verification_request.fail', $body['email']);
            $this->abort(409, 'Unable to create verification: Wait time not yet elapsed');
        } catch (DoesNotExistException|UnexpectedException $e) {
            $this->abort(500, 'Unable to create verification: Unexpected error', $e);
        }

        $this->respond(201);

    }

    /**
     * Verify new user verification TOTP and respond with 200 HTTP status code.
     * Executes rbac.user.verified event.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public function verification(): void
    {

        if ($this->apiService->getConfig('user.verification.enabled') === false) {
            $this->abort(404);
        }

        // Require headers
        $this->requireHeaders([
            'Content-Type' => 'application/json',
        ]);

        $body = $this->getBody([
            'email' => 'required|email|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.user.verification.fail', false);

        if ($user->isVerified()) {
            $this->events->doEvent('api.user.verification.fail', $body['email']);
            $this->abort(401, 'Unable to verify user: User is already verified');
        }

        // Verify then delete TOTP

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_verification);
        } catch (DoesNotExistException) {
            $this->events->doEvent('api.user.verification.fail', $body['email']);
            $this->abort(401, 'Unable to verify user: Invalid verification token');
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $body['token'])) {
            $this->events->doEvent('api.user.verification.fail', $body['email']);
            $this->abort(401, 'Unable to verify user: Invalid verification token');
        }

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_verification);

        // Verify user

        $usersModel = new UsersModel($this->rbacService);

        $usersModel->verify($body['email']);

        $this->respond();

    }

}