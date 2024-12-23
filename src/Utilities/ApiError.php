<?php

namespace Bayfront\BonesService\Api\Utilities;

use Bayfront\Bones\Application\Utilities\App;
use Bayfront\Bones\Application\Utilities\Constants;
use Bayfront\Bones\Exceptions\UndefinedConstantException;
use Bayfront\BonesService\Api\Schemas\ErrorResource;
use Bayfront\HttpResponse\Response;
use Throwable;

class ApiError
{

    /**
     * Respond with ErrorResource.
     *
     * This method can be used inside the exception handler respond() method.
     *
     * @param Response $response
     * @param Throwable $e
     * @param array $links
     * @return void
     */
    public static function respond(Response $response, Throwable $e, array $links = []): void
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

        $message = $e->getMessage();

        if ($status['code'] == 500 && !App::isDebug()) { // Hide potentially sensitive information from uncaught exceptions
            $message = 'Unexpected error';
        }

        $response->sendJson(ErrorResource::create([
            'status' => $status['code'],
            'title' => $status['phrase'],
            'message' => $message,
            'link' => $links[$code] ?? null,
            'code' => $e->getCode(),
            'request_id' => $request_id,
            'elapsed' => App::getElapsedTime(),
            'time' => date('c')
        ]));

    }

}