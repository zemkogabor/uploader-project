<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Repository\FileRepository;
use App\Settings;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;

class TusHookController
{
    public function __construct(protected LoggerInterface $logger, protected EntityManager $em, protected Settings $settings)
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function actionIndex(ServerRequestInterface $request): ResponseInterface
    {
        $fileRepository = new FileRepository($this->logger, $this->em);

        $body = $request->getParsedBody();

        switch ($request->getHeader('hook-name')[0] ?? null) {
            case 'pre-create':
                $originalRequestHeader = $body['HTTPRequest']['Header'] ?? null;

                if ($originalRequestHeader === null) {
                    throw new HttpBadRequestException($request, 'Original HTTP Request Header missing.');
                }

                $authorization = $originalRequestHeader['Authorization'] ?? null;

                if ($authorization === null) {
                    throw new HttpBadRequestException($request, 'Authorization missing.');
                }

                $client = new Client();

                try {
                    $client->get($this->settings->getAuthUrl() . '/user', [
                        RequestOptions::HEADERS => [
                            'Authorization' => $authorization,
                        ],
                        RequestOptions::TIMEOUT => 5,
                    ]);
                } catch (BadResponseException $e) {
                    $this->logger->warning('Tus pre-create hook validation failed.', [
                        'authorization' => $authorization,
                        'responseCode' => $e->getResponse()->getStatusCode(),
                    ]);

                    return $e->getResponse();
                }

                $this->logger->debug('Tus pre-create hook validation success.', [
                    'authorization' => $authorization,
                ]);

                break;
            case 'post-create':
            case 'pre-finish':
            case 'post-finish':
            case 'post-terminate':
            case 'post-receive':
                // No operation needed.
                break;
            default:
                throw new HttpBadRequestException($request, 'Invalid hook-name in header.');
        }

        return new Response();
    }
}
