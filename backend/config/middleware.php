<?php

declare(strict_types = 1);

use App\ErrorRender\JsonErrorRenderer;
use App\Middleware\CorsMiddleware;
use App\Settings;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;

return static function (App $app) {
    // Routing
    $app->options('/{routes:.+}', function (ServerRequestInterface $request, Response $response) {
        return $response;
    });
    $app->add(new CorsMiddleware());
    $app->addRoutingMiddleware();

    // Body parsing
    $app->addBodyParsingMiddleware();

    /**
     * @var Settings $settings
     */
    $settings = $app->getContainer()->get(Settings::class);
    // Error handler
    $isDev = $settings->getAppEnv() === 'dev';
    $errorMiddleware = $app->addErrorMiddleware($isDev, true, true, $app->getContainer()->get(LoggerInterface::class));
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('application/json', JsonErrorRenderer::class);
    $errorHandler->forceContentType('application/json');
};
