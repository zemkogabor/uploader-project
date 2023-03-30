<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Auth\Auth;
use App\Form\FileListForm;
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

class FileController extends BaseController
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
     * Auth validation
     * TODO: Auth validation in middleware?
     *
     * @param ServerRequestInterface $request
     * @return void
     * @throws GuzzleException
     */
    public function validateAuth(ServerRequestInterface $request): void
    {
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

            throw $e;
        }
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
            try {
                $this->validateAuth($request);
            } catch (BadResponseException $e) {
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

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function actionList(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        try {
            $this->validateAuth($request);
        } catch (BadResponseException $e) {
            return $e->getResponse();
        }

        $form = new FileListForm();
        $form->currentPage = $request->getQueryParams()['currentPage'] ?? null;
        $form->pageSize = $request->getQueryParams()['pageSize'] ?? null;
        $form->orderBy = $request->getQueryParams()['orderBy'] ?? null;
        $form->sortDesc = $request->getQueryParams()['sortDesc'] ?? null;

        $errors = static::getValidator()->validate($form);

        if ($errors->count() > 0) {
            throw new HttpBadRequestException($request, $errors[0]->getMessage());
        }

        $fileRepository = new FileRepository($this->logger, $this->em);

        $criteria = ['deleted_at' => null];
        $totalCount = $fileRepository->count($criteria);
        $files = $fileRepository->findBy(
            $criteria,
            [$form->getOrderByColumn() => $form->getSortDirection()],
            $form->getLimit(),
            $form->getOffset(),
        );

        $items = [];
        foreach ($files as $file) {
            $items[] = [
                'uuid' => $file->uuid,
                'name' => $file->filename,
                // TODO: Set timezone globally from env.
                'createdAt' => $file->created_at->setTimezone(new \DateTimeZone('Europe/Budapest'))->format('Y-m-d H:i:sP'),
            ];
        }

        // TODO: Use Symfony json response lib or one of similar lib.
        $response->getBody()->write(json_encode(['items' => $items, 'totalCount' => $totalCount]));
        return $response->withHeader('content-type', 'application/json');
    }
}
