<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Auth\Auth;
use App\Repository\FileRepository;
use App\Settings;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;

class FileController
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
     * @param ServerRequestInterface $request
     * @return string|null
     */
    protected static function getAccessToken(ServerRequestInterface $request): ?string
    {
        // Access token in header is stronger.
        $tokenFromHeader = $request->getHeader('Authorization')[0] ?? null;
        if ($tokenFromHeader !== null) {
            return $tokenFromHeader;
        }

        $tokenFromQuery = $request->getQueryParams()['accessToken'] ?? null;
        if ($tokenFromQuery !== null) {
            return $tokenFromQuery;
        }

        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param array $args
     * @return MessageInterface
     * @throws GuzzleException
     */
    public function actionDownload(ServerRequestInterface $request, ResponseInterface $response, array $args): MessageInterface
    {
        $uuid = $args['uuid'];

        if (!Uuid::isValid($uuid)) {
            throw new HttpBadRequestException($request, 'Invalid uuid.');
        }

        $fileRepository = new FileRepository($this->logger, $this->em);
        $fileEntity = $fileRepository->findOneBy([
            'uuid' => $uuid,
        ]);

        if ($fileEntity->is_private) {
            $accessToken = static::getAccessToken($request);

            if ($accessToken === null) {
                throw new HttpBadRequestException($request,'Access token missing.');
            }

            $auth = new Auth($this->settings);

            try {
                $auth->validateAccessToken($accessToken);
            } catch (BadResponseException $e) {
                $this->logger->warning('File access auth validation failed.', [
                    'authToken' => $accessToken,
                    'responseCode' => $e->getResponse()->getStatusCode(),
                ]);

                return $e->getResponse();
            }
        }

        // https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-stream-wrapper.html
        $this->s3Client->registerStreamWrapper();

        // Streams opened in “r” mode only allow data to be read from the stream, and are not seekable by default.
        $context = stream_context_create([
            's3' => ['seekable' => true],
        ]);

        $stream = new Stream(
            fopen($fileEntity->getS3Path(), 'r', false, $context),
        );
        $response = $response->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=' . $fileEntity->filename)
            ->withHeader('Content-Length', filesize($fileEntity->getS3Path()))
            ->withBody($stream);

        if ($fileEntity->is_private) {
            $response = $response->withHeader('Cache-Control', 'private');
        } else {
            $response = $response->withHeader('Cache-Control', 'public');
        }

        return $response;
    }
}
