<?php

declare(strict_types = 1);

use App\Controller\IndexController;
use App\Controller\TusHookController;
use Slim\App;

return static function (App $app) {
    $app->get('/', [IndexController::class, 'actionIndex']);
    $app->post('/tus', [TusHookController::class, 'actionIndex']);
};
