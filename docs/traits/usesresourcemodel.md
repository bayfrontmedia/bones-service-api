# [API service](../README.md) > [Traits](README.md) > UsesResourceModel

The `UsesResourceModel` trait provides helpful methods for working with an ORM service [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md).

Methods include:

- [getFieldParserRules](#getfieldparserrules)
- [getQueryParserRules](#getqueryparserrules)
- [getResourceBody](#getresourcebody)
- [createResource](#createresource)
- [listResources](#listresources)
- [readResource](#readresource)
- [updateResource](#updateresource)
- [deleteResource](#deleteresource)
- [resourceExists](#resourceexists)

## getFieldParserRules

**Description:**

Get [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/fieldparser.md) rules.

**Parameters:**

- (none)

**Returns:**

- (array)

## getQueryParserRules

**Description:**

Get [FieldParser](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/utilities/queryparser.md) rules.

**Parameters:**

- (none)

**Returns:**

- (array)

## getResourceBody

**Description:**

Get only and validate writable fields from body.

Optionally ensure all required fields exist (on create).

Optionally ensure predefined values do not exist, then set their value.
Helpful when values are set by path parameters.

Optionally ensure disallowed fields do not exist (on update).
Helpful for scoped resources whose scoped values are set by path parameters.

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$validate_required_fields = false` (bool)
- `$defined_values = []` (array): Predefined values not allowed to be defined in body
- `$disallowed_fields = []` (array)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## createResource

**Description:**

Create new [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) resource.

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$fields` (array)

**Returns:**

- (array): Created resource

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`

## listResources

**Description:**

List [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) resources, 
including pagination and aggregate of requested.

Returned array keys:

- `list`: Collection list
- `config`: Schema configuration array with pagination and aggregate data

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$query_filter = []`: Additional filters to apply to query

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`

## readResource

**Description:**

Read [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) resource.

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$primary_key_id` (mixed)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`



## updateResource

**Description:**

Update [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) resource.

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$primary_key_id` (mixed)
- `$fields` (array)

**Returns:**

- (array)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`
- `Bayfront\BonesService\Api\Exceptions\Http\BadRequestException`
- `Bayfront\BonesService\Api\Exceptions\Http\ConflictException`
- `Bayfront\BonesService\Api\Exceptions\Http\NotFoundException`

## deleteResource

**Description:**

Delete [ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md) resource.

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$primary_key_id` (mixed)

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`

## resourceExists

**Description:**

Does resource exist?

**Parameters:**

- `$resourceModel` ([ResourceModel](https://github.com/bayfrontmedia/bones-service-orm/blob/master/docs/models/resourcemodel.md))
- `$primary_key_id` (mixed)
- `$query_filter = []`

**Returns:**

- (bool)

**Throws:**

- `Bayfront\BonesService\Api\Exceptions\ApiServiceException`