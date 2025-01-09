<?php

namespace Bayfront\BonesService\Api\Traits;

use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Orm\Models\ResourceModel;
use Bayfront\BonesService\Rbac\Models\UsersModel;

trait UserResource
{

    use UsesResourceModel;

    /**
     * Validate user exists.
     *
     * @param UsersModel $usersModel
     * @param string $user_id
     * @return void
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    protected function validateUserExists(UsersModel $usersModel, string $user_id): void
    {

        try {

            if (!$usersModel->exists($user_id)) {
                throw new NotFoundException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException($e->getMessage());
        }

    }

    /**
     * Does user scoped resource exist?
     *
     * @param ResourceModel $resourceModel
     * @param string $user_id (With database column of "user")
     * @param string $resource_id
     * @return bool
     * @throws ApiServiceException
     */
    protected function userResourceExists(ResourceModel $resourceModel, string $user_id, string $resource_id): bool
    {

        return $this->resourceExists($resourceModel, $resource_id, [
            [
                'user' => [
                    'eq' => $user_id
                ]
            ]
        ]);

    }

    /**
     * Validate user scoped resource exists.
     *
     * @param ResourceModel $resourceModel
     * @param string $user_id (With database column of "user")
     * @param string $resource_id
     * @return void
     * @throws ApiServiceException
     * @throws NotFoundException
     */
    protected function validateUserResourceExists(ResourceModel $resourceModel, string $user_id, string $resource_id): void
    {

        if (!$this->userResourceExists($resourceModel, $user_id, $resource_id)) {
            throw new NotFoundException();
        }

    }

}