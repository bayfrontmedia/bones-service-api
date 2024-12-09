<?php

namespace Bayfront\BonesService\Api\Utilities;

use Bayfront\ArrayHelpers\Arr;
use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Constants;
use Bayfront\Bones\Exceptions\UndefinedConstantException;
use Bayfront\BonesService\Api\Exceptions\ApiHttpException;
use Bayfront\BonesService\Api\Exceptions\ApiServiceException;
use Bayfront\BonesService\Api\Exceptions\Http\BadRequestException;
use Bayfront\BonesService\Api\Exceptions\Http\ConflictException;
use Bayfront\BonesService\Api\Exceptions\Http\ForbiddenException;
use Bayfront\BonesService\Api\Exceptions\Http\MethodNotAllowedException;
use Bayfront\BonesService\Api\Exceptions\Http\NotAcceptableException;
use Bayfront\BonesService\Api\Exceptions\Http\NotFoundException;
use Bayfront\BonesService\Api\Exceptions\Http\PaymentRequiredException;
use Bayfront\BonesService\Api\Exceptions\Http\TooManyRequestsException;
use Bayfront\BonesService\Api\Exceptions\Http\UnauthorizedException;
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

    /**
     * Throw API exception based on status code.
     *
     * @param int $status_code
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     * @return no-return
     * @throws ApiHttpException
     * @throws ApiServiceException
     */
    public static function abort(int $status_code, string $message = '', int $code = 0, Throwable $previous = null): void
    {

        $exceptions = [
            400 => BadRequestException::class,
            401 => UnauthorizedException::class,
            402 => PaymentRequiredException::class,
            403 => ForbiddenException::class,
            404 => NotFoundException::class,
            405 => MethodNotAllowedException::class,
            406 => NotAcceptableException::class,
            409 => ConflictException::class,
            429 => TooManyRequestsException::class
        ];

        if (isset($exceptions[$status_code])) {
            throw new $exceptions[$status_code]($message, $code, $previous);
        }

        throw new ApiServiceException($message, $code, $previous);

    }

}