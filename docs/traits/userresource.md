# [API service](../README.md) > [Traits](README.md) > UserResource

The `UserResource` trait is used when working with user-scoped resources

Methods include:

- [validateUserExists](#validateuserexists)
- [userResourceExists](#userresourceexists)
- [validateUserResourceExists](#validateuserresourceexists)

## validateUserExists

**Description:**

Validate user exists.

**Parameters:**

- `$usersModel` (`UsersModel`)
- `$user_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## userResourceExists

**Description:**

Does user scoped resource exist?

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$user_id` (string): With database column of `user`
- `$resource_id` (string)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`

## validateUserResourceExists

**Description:**

Validate user scoped resource exists.

**Parameters:**

- `$resourceModel` (`ResourceModel`)
- `$user_id` (string): With database column of `user`
- `$resource_id` (string)

**Returns:**

- (void)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`