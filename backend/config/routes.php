<?php

declare(strict_types = 1);

use App\Controller\CustomerController;
use App\Controller\FileController;
use App\Controller\IndexController;
use App\Controller\TusHookController;
use App\Middleware\AuthMiddleware;
use App\Settings;
use Slim\App;

return static function (App $app) {
    /**
     * @var Settings $settings
     */
    $settings = $app->getContainer()->get(Settings::class);

    $app->get('/', [IndexController::class, 'actionIndex']);
    $app->post('/tus', [TusHookController::class, 'actionIndex']);
    $app->get('/file/download/{uuid}', [FileController::class, 'actionDownload'])->add(new AuthMiddleware($settings));
    $app->get('/file/list', [FileController::class, 'actionList'])->add(new AuthMiddleware($settings));
};
