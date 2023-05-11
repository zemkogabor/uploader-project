<?php

declare(strict_types = 1);

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class IndexController extends BaseController
{
    public function __construct(protected LoggerInterface $logger)
    {
    }

    public function actionIndex(ServerRequest $request, Response $response): Response
    {
        return $response->withJson(['title' => 'Uploader Project']);
    }
}
