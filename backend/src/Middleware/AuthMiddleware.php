<?php

declare(strict_types = 1);

namespace App\Middleware;

use App\Auth\Auth;
use App\Settings;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Http\ServerRequest;

class AuthMiddleware
{
    public function __construct(protected Settings $settings)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function __invoke(ServerRequest $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $accessToken = Auth::getAccessTokenFromRequest($request);

        if ($accessToken === null) {
            throw new HttpBadRequestException($request,'Access token missing.');
        }

        $response = $handler->handle($request);
        $auth = new Auth($this->settings);

        try {
            $auth->validateAccessToken($accessToken);
        } catch (BadResponseException $e) {
            return $e->getResponse();
        }

        return $response;
    }
}
