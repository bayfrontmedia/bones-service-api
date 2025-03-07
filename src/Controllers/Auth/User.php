<?php

namespace Bayfront\BonesService\Api\Controllers\Auth;

use Bayfront\BonesService\Api\Controllers\Abstracts\AuthApiController;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException;
use Bayfront\BonesService\Api\Models\ApiModel;
use Bayfront\BonesService\Api\Schemas\UserResource;
use Bayfront\BonesService\Api\Traits\UsesResourceModel;
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

    use UsesResourceModel;

    /**
     * Authenticate email or abort.
     *
     * @param string $email
     * @param string $fail_event
     * @param bool $check_verified
     * @return RbacUser
     * @throws ApiServiceException
     * @throws UnauthorizedException
     */
    private function authenticateEmail(string $email, string $fail_event, bool $check_verified = true): RbacUser
    {

        $authenticator = new EmailAuthenticator($this->rbacService);

        try {
            return $authenticator->authenticate($email, $check_verified);
        } catch (UserDoesNotExistException|UserDisabledException|UserNotVerifiedException) {
            $this->events->doEvent($fail_event, $email);
            throw new UnauthorizedException();
        } catch (UnexpectedAuthenticationException $e) {
            throw new ApiServiceException('Unexpected error', 0, $e);
        }

    }

    /**
     * Register new user.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws ConflictException
     * @throws NotFoundException
     */
    public function register(): void
    {

        if ($this->apiService->getConfig('user.allow_register') !== true) {
            throw new NotFoundException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $usersModel = new UsersModel($this->rbacService);

        $body = $this->getResourceBody($usersModel, true, [
            'admin' => false,
            'enabled' => true
        ]);

        $resource = $this->createResource($usersModel, $body);

        $this->respond(201, UserResource::create($resource));

    }

    /**
     * Request password reset token.
     * Executes api.user.password_request event.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws TooManyRequestsException
     */
    public function passwordRequest(): void
    {

        if ($this->apiService->getConfig('user.password_request.enabled') !== true) {
            throw new NotFoundException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255'
        ]);

        try {
            $user = $this->authenticateEmail($body['email'], 'api.user.password_request.fail');
        } catch (UnauthorizedException) {
            $this->respond(204);
            return;
        }

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
            throw new TooManyRequestsException();
        } catch (DoesNotExistException|UnexpectedException $e) {
            throw new ApiServiceException('Unexpected error', 0, $e);
        }

        $this->events->doEvent('api.user.password_request', $user, $totp);
        $this->respond(204);

    }

    /**
     * Reset password.
     * Executes rbac.user.password.updated event.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function resetPassword(): void
    {

        if ($this->apiService->getConfig('user.password_request.enabled') !== true) {
            throw new NotFoundException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255',
            'password' => 'required|isString|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.user.password.fail');

        // Validate token

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        } catch (DoesNotExistException) { // Token does not exist
            $this->events->doEvent('api.user.password.fail', $body['email']);
            throw new UnauthorizedException();
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $body['token'])) { // Invalid token value
            $this->events->doEvent('api.user.password.fail', $body['email']);
            throw new UnauthorizedException();
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
            throw new ApiServiceException('Unexpected error', 0, $e);
        }

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_password);
        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_tfa);
        $userMetaModel->deleteAllTokens($user->getId());

        $this->respond(204);

    }

    /**
     * Request new user verification token.
     * Executes api.user.verification_request event.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws TooManyRequestsException
     * @throws UnauthorizedException
     */
    public function verificationRequest(): void
    {

        if ($this->apiService->getConfig('user.verification.enabled') === false) {
            throw new NotFoundException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.user.verification_request.fail', false);

        if ($user->isVerified()) {
            $this->events->doEvent('api.user.verification_request.fail', $body['email']);
            $this->respond(204);
            return;
        }

        $apiModel = new ApiModel($this->apiService);

        try {

            $apiModel->createUserVerificationRequest($user->getResource());

        } catch (AlreadyExistsException) {
            $this->events->doEvent('api.user.verification_request.fail', $body['email']);
            throw new TooManyRequestsException();
        } catch (DoesNotExistException|UnexpectedException $e) {
            throw new ApiServiceException('Unexpected error', 0, $e);
        }

        $this->respond(204);

    }

    /**
     * Verify new user verification token.
     * Executes rbac.user.verified event.
     *
     * @return void
     * @throws ApiServiceException
     * @throws BadRequestException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function verify(): void
    {

        if ($this->apiService->getConfig('user.verification.enabled') === false) {
            throw new NotFoundException();
        }

        $this->validateHeaders([
            'Content-Type' => 'required|matches:application/json'
        ]);

        $body = $this->getJsonBody([
            'email' => 'required|email|maxLength:255',
            'token' => 'required|isString'
        ]);

        $user = $this->authenticateEmail($body['email'], 'api.user.verification.fail', false);

        if ($user->isVerified()) {
            $this->events->doEvent('api.user.verification.fail', $body['email']);
            $this->respond(204);
            return;
        }

        // Verify then delete TOTP

        $userMetaModel = new UserMetaModel($this->rbacService);

        try {
            $totp = $userMetaModel->getTotp($user->getId(), $userMetaModel->totp_meta_key_verification);
        } catch (DoesNotExistException) {
            $this->events->doEvent('api.user.verification.fail', $body['email']);
            throw new UnauthorizedException();
        }

        if (!$this->rbacService->hashMatches($totp->getValue(), $body['token'])) { // Invalid token value
            $this->events->doEvent('api.user.verification.fail', $body['email']);
            throw new UnauthorizedException();
        }

        $userMetaModel->deleteTotp($user->getId(), $userMetaModel->totp_meta_key_verification);

        // Verify user

        $usersModel = new UsersModel($this->rbacService);

        $usersModel->verify($body['email']);

        $this->respond(204);

    }

}