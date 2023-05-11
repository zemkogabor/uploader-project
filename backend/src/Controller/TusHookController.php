<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Auth\Auth;
use App\Entity\FileEntity;
use App\Repository\FileRepository;
use App\Settings;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpBadRequestException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

class TusHookController extends BaseController
{
    public function __construct(
        protected LoggerInterface $logger,
        protected EntityManager $em,
        protected Settings $settings,
        protected S3Client $s3Client,
    )
    {
    }

    /**
     * @param ServerRequest $request
     * @param Response $response
     * @return ResponseInterface
     * @throws GuzzleException
     * @throws ORMException
     */
    public function actionIndex(ServerRequest $request, Response $response): ResponseInterface
    {
        $body = $request->getParsedBody();

        $hookName = $request->getHeader('hook-name')[0] ?? null;
        switch ($hookName) {
            case 'pre-create':
                $originalRequestHeader = $body['HTTPRequest']['Header'] ?? null;

                if ($originalRequestHeader === null) {
                    throw new HttpBadRequestException($request, 'Original HTTP Request Header missing.');
                }

                $authorization = $originalRequestHeader['Authorization'] ?? null;

                if ($authorization === null || $authorization[0] === null) {
                    throw new HttpBadRequestException($request, 'Authorization header missing.');
                }

                $auth = new Auth($this->settings);

                try {
                    $auth->validateAccessToken($authorization[0]);
                } catch (BadResponseException $e) {
                    $this->logger->warning('Tus pre-create hook validation failed.', [
                        'authorization' => $authorization,
                        'responseCode' => $e->getResponse()->getStatusCode(),
                    ]);

                    return $e->getResponse();
                }

                break;
            case 'pre-finish':
                $storageType = $body['Upload']['Storage']['Type'];
                if ($storageType !== 's3store') {
                    throw new \LogicException('Not supported storage type: ' . $storageType);
                }

                $file = new FileEntity();
                $file->bucket = $body['Upload']['Storage']['Bucket'];
                $file->key = $body['Upload']['Storage']['Key'];
                $file->filename = $body['Upload']['MetaData']['filename'];
                $file->is_private = true;

                $fileRepository = new FileRepository($this->logger, $this->em);
                $fileRepository->createFile($file);
                break;
            case 'post-finish':
                // TODO: Realtime event to notify users.
                break;
            default:
                $this->logger->warning('Not supported hook-name in header.', [
                    'name' => $hookName,
                ]);
        }

        return $response;
    }
}
