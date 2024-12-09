<?php

namespace Bayfront\BonesService\Api\Utilities;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Constants;
use Bayfront\Bones\Exceptions\UndefinedConstantException;
use Bayfront\BonesService\Api\Schemas\ErrorResource;
use Bayfront\HttpResponse\Response;
use Throwable;

class ApiError
{

    private static array $links = [];

    /**
     * Set error links.
     *
     * @param array $links
     * @return void
     */
    public static function setLinks(array $links): void
    {
        self::$links = $links;
    }

    /**
     * Get error links.
     *
     * @return array
     */
    public static function getLinks(): array
    {
        return self::$links;
    }

    /**
     * Get single error link.
     *
     * @param string $key
     * @return string|null
     */
    public static function getLink(string $key): ?string
    {
        return Arr::get(self::getLinks(), $key);
    }

    /**
     * Respond with API error resource.
     *
     * This method can be used inside the exception handler respond() method.
     *
     * @param Response $response
     * @param Throwable $e
     * @return void
     */
    public static function respond(Response $response, Throwable $e): void
    {

        try {
            $request_id = Constants::get('REQUEST_ID');
        } catch (UndefinedConstantException) {
            $request_id = null;
        }

        $code = $e->getCode();
        $status = $response->getStatusCode();

        if ($code == 0) { // Fallback to status code
            $code = $status['code'];
        }

        $response->sendJson(ErrorResource::create([
            'status' => $status['code'],
            'title' => $status['phrase'],
            'message' => $e->getMessage(),
            'link' => self::getLink($code),
            'code' => $e->getCode(),
            'request_id' => $request_id,
            'elapsed' => App::getElapsedTime(),
            'time' => date('c')
        ]));

    }

}