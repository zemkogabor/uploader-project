<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Form\FileListForm;
use App\Repository\FileRepository;
use App\Settings;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Slim\Exception\HttpBadRequestException;
use Slim\Http\Response;
use Slim\Http\ServerRequest;

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
     * @param ServerRequest $request
     * @param ResponseInterface $response
     * @param array $args
     * @return MessageInterface
     * @throws GuzzleException
     */
    public function actionDownload(ServerRequest $request, ResponseInterface $response, array $args): MessageInterface
    {
        $uuid = $args['uuid'];

        if (!Uuid::isValid($uuid)) {
            throw new HttpBadRequestException($request, 'Invalid uuid.');
        }

        $fileRepository = new FileRepository($this->logger, $this->em);
        $fileEntity = $fileRepository->findOneBy([
            'uuid' => $uuid,
        ]);

        // https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-stream-wrapper.html
        $this->s3Client->registerStreamWrapper();

        // Streams opened in “r” mode only allow data to be read from the stream, and are not seekable by default.
        $context = stream_context_create([
            's3' => ['seekable' => true],
        ]);

        // TODO: Check connection before download!
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
     * @param ServerRequest $request
     * @param Response $response
     * @return Response
     */
    public function actionList(ServerRequest $request, Response $response): Response
    {
        $form = new FileListForm();
        $form->currentPage = $request->getQueryParam('currentPage') ?? null;
        $form->pageSize = $request->getQueryParam('pageSize') ?? null;
        $form->orderBy = $request->getQueryParam('orderBy') ?? null;
        $form->sortDesc = $request->getQueryParam('sortDesc') ?? null;

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

        return $response->withJson(['items' => $items, 'totalCount' => $totalCount]);
    }
}
