<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Controller;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Bayfront\LeakyBucket\AdapterException;
use Bayfront\LeakyBucket\Bucket;
use Bayfront\LeakyBucket\BucketException;
use Bayfront\Sanitize\Sanitize;
use Bayfront\Validator\Validator;
use Exception;

abstract class ApiController extends Controller
{

    public ApiService $apiService;
    public EventService $events;
    public FilterService $filters;
    public RbacService $rbacService;
    public Response $response;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
        $this->events = $this->apiService->events;
        $this->filters = $this->apiService->filters;
        $this->rbacService = $this->apiService->rbacService;
        $this->response = $this->apiService->response;

        parent::__construct($this->events); // Fires the bones.controller event
        $this->events->doEvent('api.controller', $this);
    }

    /*
     * Override global API configuration
     */
    public bool $check_required_headers = true;
    public bool $check_https = true;
    public bool $check_ip_whitelist = true;
    public bool $set_required_headers = true;

    /**
     * Enforce rate limit and set X-RateLimit headers.
     *
     * @param string $id (Bucket ID)
     * @param int $limit
     * @return void
     * @throws ApiServiceException
     * @throws TooManyRequestsException
     */
    protected function enforceRateLimit(string $id, int $limit): void
    {

        try {

            /** @var Bucket $bucket */
            $bucket = App::make('Bayfront\LeakyBucket\Bucket', [
                'id' => $id,
                'settings' => [
                    'capacity' => $limit, // Total bucket capacity
                    'leak' => $limit // Number of drops to leak per minute
                ]
            ]);

        } catch (Exception $e) {
            throw new ApiServiceException($e->getMessage());
        }

        try {
            $bucket->leak()->fill()->save();
        } catch (BucketException) {

            $wait = round($bucket->getSecondsUntilCapacity());

            $this->response->setHeaders([
                'X-RateLimit-Limit' => $limit,
                'X-RateLimit-Remaining' => floor($bucket->getCapacityRemaining()),
                'X-RateLimit-Reset' => round($bucket->getSecondsUntilEmpty()),
                'Retry-After' => $wait
            ]);

            if ($this instanceof AuthApiController) {
                $this->events->doEvent('api.auth.limit');
            }

            throw new TooManyRequestsException('Rate limit exceeded. Try again in ' . $wait . ' seconds');

        } catch (AdapterException $e) {
            throw new ApiServiceException($e->getMessage());
        }

        $this->response->setHeaders([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => floor($bucket->getCapacityRemaining()),
            'X-RateLimit-Reset' => round($bucket->getSecondsUntilEmpty())
        ]);

    }

    /**
     * Validate user is admin.
     *
     * @param User $user
     * @return void
     * @throws ForbiddenException
     */
    protected function validateIsAdmin(User $user): void
    {
        if (!$user->isAdmin()) {
            throw new ForbiddenException();
        }
    }

    /**
     * Validate user is in enabled tenant.
     * Admin users have no restrictions.
     *
     * @param User $user
     * @param string $tenant_id
     * @return void
     * @throws ApiServiceException
     * @throws ForbiddenException
     */
    protected function validateInEnabledTenant(User $user, string $tenant_id): void
    {

        if ($user->isAdmin()) {
            return;
        }

        try {
            if (!$user->inEnabledTenant($tenant_id)) {
                throw new ForbiddenException();
            }
        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to verify user in enabled tenant: Unexpected error', 0, $e);
        }

    }

    /**
     * Validate user has required permissions.
     * Admin users have no restrictions.
     *
     * @param User $user
     * @param string $tenant_id
     * @param array $permission_names
     * @return void
     * @throws ApiServiceException
     * @throws ForbiddenException
     */
    protected function validateHasPermissions(User $user, string $tenant_id, array $permission_names): void
    {

        try {

            if (!$user->canDoAll($tenant_id, $permission_names)) {
                throw new ForbiddenException();
            }

        } catch (UnexpectedException $e) {
            throw new ApiServiceException('Unable to verify permissions: Unexpected error', 0, $e);
        }

    }

    /**
     * Validate path parameters against a defined set of rules.
     *
     * @param array $params
     * @param array $rules
     * @return void
     * @throws BadRequestException
     */
    protected function validatePath(array $params, array $rules = []): void
    {

        $validator = new Validator();
        $validator->validate($params, $rules, false, true);

        if (!$validator->isValid()) {

            $messages = $validator->getMessages();
            $field = array_key_first($messages);

            throw new BadRequestException('Unable to validate path (' . $field . '): Invalid or missing parameter(s)');

        }

    }

    /**
     * Validate query against a defined set of rules.
     *
     * NOTE:
     * Since the query is a string, only string related validation rules can be applied.
     *
     * @param array $rules
     * @param bool $allow_other (Allow other keys not defined in rules)
     * @return void
     * @throws BadRequestException
     */
    protected function validateQuery(array $rules = [], bool $allow_other = false): void
    {

        $query = Request::getQuery();

        if (!empty($rules) && $allow_other === false) {
            if (!empty(Arr::except($query, array_keys($rules)))) {
                throw new BadRequestException('Unable to validate query: Invalid field(s)');
            }
        }

        $validator = new Validator();
        $validator->validate($query, $rules, false, true);

        if (!$validator->isValid()) {

            $messages = $validator->getMessages();
            $field = array_key_first($messages);

            throw new BadRequestException('Unable to validate query (' . $field . '): Invalid or missing parameter(s)');

        }

    }

    /**
     * Validate headers against a defined set of rules.
     *
     * @param array $rules
     * @return void
     * @throws BadRequestException
     */
    protected function validateHeaders(array $rules = []): void
    {

        $validator = new Validator();
        $validator->validate(Request::getHeader(), $rules, false, true);

        if (!$validator->isValid()) {

            $messages = $validator->getMessages();
            $field = array_key_first($messages);

            throw new BadRequestException('Unable to validate headers (' . $field . '): Invalid or missing parameter(s)');

        }

    }

    /**
     * Validate body content exists.
     *
     * @return void
     * @throws BadRequestException
     */
    protected function validateHasBody(): void
    {
        if (Request::getBody() == '') {
            throw new BadRequestException('Unable to validate body: Missing content');
        }
    }

    /**
     * Validate array contains all fields.
     *
     * Helpful when validation rules do not include "required",
     * such as with a ResourceModel.
     *
     * @param array $array
     * @param array $keys
     * @return void
     * @throws BadRequestException
     */
    protected function validateFieldsExist(array $array, array $keys): void
    {
        if (Arr::isMissing($array, $keys)) {
            throw new BadRequestException('Bad request: Missing required field(s)');
        }
    }

    /**
     * Validate array does not contain any fields.
     *
     * @param array $array
     * @param array $keys
     * @return void
     * @throws BadRequestException
     */
    protected function validateFieldsDoNotExist(array $array, array $keys): void
    {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                throw new BadRequestException('Bad request: Invalid field (' . $key . ')');
            }
        }
    }

    /**
     * Process rules and return body.
     *
     * @param array $fields
     * @param array $rules
     * @param bool $allow_other (Allow other keys not defined in rules)
     * @return array
     * @throws BadRequestException
     */
    private function processRules(array $fields, array $rules, bool $allow_other): array
    {

        if (!empty($rules) && $allow_other === false) {
            if (!empty(Arr::except($fields, array_keys($rules)))) {
                throw new BadRequestException('Unable to validate fields: Invalid field(s)');
            }
        }

        if (!empty($rules)) {

            $validator = new Validator();
            $validator->validate($fields, $rules, false, true);

            if (!$validator->isValid()) {

                $messages = $validator->getMessages();
                $field = array_key_first($messages);

                throw new BadRequestException('Unable to validate fields: Invalid or missing field (' . $field . ')');

            }

        }

        return $fields;

    }

    /**
     * Validate and return POST data.
     *
     * Since POST data is received as a string, the $cast_fields array
     * allows fields to be cast to another expected type before
     * processing rules.
     *
     * Types include:
     * - array (From JSON object)
     * - boolean
     * - float
     * - integer
     * - null (If empty string)
     *
     * @param array $rules
     * @param bool $allow_other (Allow other keys not defined in rules)
     * @param array $cast_fields (Key/value pair of field/type)
     * @return array
     * @throws BadRequestException
     */
    protected function getPostData(array $rules = [], bool $allow_other = false, array $cast_fields = []): array
    {

        $data = Request::getPost();

        if (!is_array($data)) {
            throw new BadRequestException('Unable to validate POST data: Invalid or missing data');
        }

        foreach ($cast_fields as $field => $cast) {

            if (isset($data[$field])) {

                if ($cast === 'array') {
                    $data[$field] = json_decode($data[$field], true);
                } else if ($cast === 'boolean') {
                    $data[$field] = Sanitize::cast($data[$field], Sanitize::CAST_BOOL);
                } else if ($cast === 'float') {
                    $data[$field] = Sanitize::cast($data[$field], Sanitize::CAST_FLOAT);
                } else if ($cast === 'integer') {
                    $data[$field] = Sanitize::cast($data[$field], Sanitize::CAST_INT);
                } else if ($cast === 'null' && $data[$field] == '') {
                    $data[$field] = null;
                } else {
                    throw new BadRequestException('Unable to validate POST data: Invalid cast field');
                }

            }

        }

        return $this->processRules($data, $rules, $allow_other);

    }

    /**
     * Validate and return form URL encoded body.
     *
     * @param array $rules
     * @param bool $allow_other (Allow other keys not defined in rules)
     * @return array
     * @throws BadRequestException
     */
    protected function getFormEncodedBody(array $rules = [], bool $allow_other = false): array
    {

        $keys = explode('&', Request::getBody());

        $body = [];

        foreach ($keys as $key) {

            $val = explode('=', $key, 2);

            if (isset($val[1])) {
                $body[$val[0]] = $val[1];
            }

        }

        return $this->processRules($body, $rules, $allow_other);

    }

    /**
     * Validate and return JSON body.
     *
     * @param array $rules
     * @param bool $allow_other (Allow other keys not defined in rules)
     * @return array
     * @throws BadRequestException
     */
    protected function getJsonBody(array $rules = [], bool $allow_other = false): array
    {

        $body = json_decode(Request::getBody(), true);

        if (!$body || !is_array($body)) {
            throw new BadRequestException('Unable to validate body: Invalid or missing JSON');
        }

        return $this->processRules($body, $rules, $allow_other);

    }

    /**
     * Validate and return plaintext body.
     *
     * @param array $rules (Rules with key of "body")
     * @return string
     * @throws BadRequestException
     */
    protected function getTextBody(array $rules = []): string
    {

        $body = Request::getBody();

        if (!empty($rules)) {

            $validator = new Validator();
            $validator->validate([
                'body' => $body
            ], $rules, false, true);

            if (!$validator->isValid()) {
                throw new BadRequestException('Unable to validate body: Invalid body value');
            }

        }

        return $body;

    }

    /**
     * Send API response.
     *
     * - Filters response using the api.response filter
     * - Triggers the api.response event
     * - Sends $data as JSON encoded string
     *
     * @param int $status_code (HTTP status code to send)
     * @param array $data (Data to send)
     * @param array $headers (Key/value pairs of headers to send)
     * @return void
     * @throws ApiServiceException
     */
    protected function respond(int $status_code = 200, array $data = [], array $headers = []): void
    {

        $data = (array)$this->filters->doFilter('api.response', $data);

        try {

            $this->response->setStatusCode($status_code)->setHeaders($headers);

            if (!empty($data)) { // Only setBody if one exists
                $this->response->setBody(json_encode($data));
            }

        } catch (InvalidStatusCodeException) {
            throw new ApiServiceException('Unable to respond: Invalid status code (' . $status_code . ')');
        }

        $this->events->doEvent('api.response', $this);

        $this->response->send();

    }

}