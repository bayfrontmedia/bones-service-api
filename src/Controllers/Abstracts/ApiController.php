<?php

namespace Bayfront\BonesService\Api\Controllers\Abstracts;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Abstracts\Controller;
use Bayfront\Bones\Application\Services\Events\EventService;
use Bayfront\Bones\Application\Services\Filters\FilterService;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Helpers;
use Bayfront\BonesService\Api\ApiService;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Traits\Auditable;
use Bayfront\BonesService\Api\Utilities\ApiError;
use Bayfront\BonesService\Orm\Exceptions\UnexpectedException;
use Bayfront\BonesService\Rbac\RbacService;
use Bayfront\BonesService\Rbac\User;
use Bayfront\HttpRequest\Request;
use Bayfront\HttpResponse\InvalidStatusCodeException;
use Bayfront\HttpResponse\Response;
use Bayfront\LeakyBucket\AdapterException;
use Bayfront\LeakyBucket\Bucket;
use Bayfront\LeakyBucket\BucketException;
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

    /*
     * OpenAPI security scheme type
     */
    public const OPENAPI_SECURITY_HTTP = 'http';
    public const OPENAPI_SECURITY_KEY = 'apiKey';

    /**
     * Enforce rate limit and set X-RateLimit headers.
     * On error, aborts with 429 HTTP status.
     *
     * @param string $id
     * @param int $limit
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
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
            ApiError::abort(500, $e->getMessage());
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

            ApiError::abort(429, 'Rate limit exceeded. Try again in ' . $wait . ' seconds');

        } catch (AdapterException $e) {
            ApiError::abort(500, $e->getMessage());
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
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function validateIsAdmin(User $user): void
    {
        if (!$user->isAdmin()) {
            ApiError::abort(403);
        }
    }

    /**
     * Validate user has required permissions.
     *
     * @param User $user
     * @param string $tenant_id
     * @param array $permission_names
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function validatePermissions(User $user, string $tenant_id, array $permission_names): void
    {

        if ($user->isAdmin()) {
            return;
        }

        try {

            if (!$user->canDoAll($tenant_id, $permission_names)) {
                ApiError::abort(403);
            }

        } catch (UnexpectedException $e) {
            ApiError::abort(500, 'Unable to verify permissions: Unexpected error', 0, $e);
        }

    }

    /**
     * Validate path parameters against a defined set of rules.
     *
     * @param array $params
     * @param array $rules
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function validatePath(array $params, array $rules = []): void
    {

        $validator = new Validator();
        $validator->validate($params, $rules, false, true);

        if (!$validator->isValid()) {

            $messages = $validator->getMessages();
            $field = array_key_first($messages);

            ApiError::abort(400, 'Unable to validate path (' . $field . '): Invalid or missing parameter(s)');

        }

    }

    /**
     * Validate query against a defined set of rules.
     *
     * NOTE:
     * Since the query is a string, only string related validation rules can be applied.
     *
     * @param array $rules
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function validateQuery(array $rules = []): void
    {

        $validator = new Validator();
        $validator->validate(Request::getQuery(), $rules, false, true);

        if (!$validator->isValid()) {

            $messages = $validator->getMessages();
            $field = array_key_first($messages);

            ApiError::abort(400, 'Unable to validate query (' . $field . '): Invalid or missing parameter(s)');

        }

    }

    /**
     * Validate headers against a defined set of rules.
     *
     * @param array $rules
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function validateHeaders(array $rules = []): void
    {

        $validator = new Validator();
        $validator->validate(Request::getHeader(), $rules, false, true);

        if (!$validator->isValid()) {

            $messages = $validator->getMessages();
            $field = array_key_first($messages);

            ApiError::abort(400, 'Unable to validate headers (' . $field . '): Invalid or missing parameter(s)');

        }

    }

    /**
     * Validate body content exists.
     *
     * @return void
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function validateBodyExists(): void
    {
        if (Request::getBody() == '') {
            ApiError::abort(400, 'Unable to validate body: Missing content');
        }
    }

    /**
     * Validate and return form URL encoded body.
     *
     * @param array $rules
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function getFormEncodedBody(array $rules = []): array
    {

        $keys = explode('&', Request::getBody());

        $body = [];

        foreach ($keys as $key) {

            $val = explode('=', $key, 2);

            if (isset($val[1])) {
                $body[$val[0]] = $val[1];
            }

        }

        if (!empty($rules)) {

            $validator = new Validator();
            $validator->validate($body, $rules, false, true);

            if (!$validator->isValid()) {

                $messages = $validator->getMessages();
                $field = array_key_first($messages);

                ApiError::abort(400, 'Unable to validate body (' . $field . '): Invalid or missing parameter(s)');

            }

        }

        return $body;

    }

    /**
     * Validate and return JSON body.
     *
     * @param array $rules
     * @return array
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    protected function getJsonBody(array $rules = []): array
    {

        $body = json_decode(Request::getBody(), true);

        if (!$body || !is_array($body)) {
            ApiError::abort(400, 'Unable to validate body: Invalid or missing JSON');
        }

        if (!empty($rules)) {

            $validator = new Validator();
            $validator->validate($body, $rules, false, true);

            if (!$validator->isValid()) {

                $messages = $validator->getMessages();
                $field = array_key_first($messages);

                ApiError::abort(400, 'Unable to validate body (' . $field . '): Invalid or missing parameter(s)');

            }

        }

        return $body;

    }

    /**
     * Validate and return plaintext body.
     *
     * @param array $rules (Rules with key = body)
     * @return string
     * @throws ApiHttpException
     * @throws ApiServiceException
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

                $messages = $validator->getMessages();
                $field = array_key_first($messages);

                ApiError::abort(400, 'Unable to validate body (' . $field . '): Invalid or missing parameter(s)');

            }

        }

        return $body;

    }

    /**
     * Send API response.
     *
     * - Filters response using the api.response filter
     * - Triggers the api.auditable event if needed
     * - Triggers the api.response event
     * - Sends $data as json_encoded string
     *
     * @param int $status_code (HTTP status code to send)
     * @param array $data (Data to send)
     * @param array $headers (Key/value pairs of header values to send)
     * @return void
     * @throws ApiHttpException
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
            ApiError::abort(500, 'Unable to respond: Invalid status code (' . $status_code . ')');
        }

        if (in_array(Auditable::class, Helpers::classUses($this))) {

            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

            /** @var Auditable $this */

            if (in_array(Arr::get($backtrace, '1.function'), $this->getAuditableFunctions())) {
                $this->events->doEvent('api.auditable', $this, Arr::get($backtrace, '1.class', ''), Arr::get($backtrace, '1.function', ''));
            }

        }

        $this->events->doEvent('api.response', $this);

        $this->response->send();

    }

}