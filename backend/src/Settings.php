<?php

declare(strict_types = 1);

namespace App;

use Psr\Log\LogLevel;
use Ramsey\Uuid\Doctrine\UuidType;

class Settings
{
    private string $appEnv;
    private array $logger;
    private string|null $diCompilationPath;
    private array $doctrine;
    private string $authUrl;
    private array $s3Client;

    public function __construct(string $appEnv)
    {
        $this->appEnv = $appEnv;
        $this->diCompilationPath = __DIR__ . '/../var/cache/di_compilation';
        $this->logger = [
            'name' => 'general',
            'path' => $_ENV['LOGGER_PATH'] ?? 'php://stdout',
            'level' => $_ENV['LOGGER_LEVEL'] ?? LogLevel::DEBUG,
        ];
        $this->doctrine = [
            'dev_mode' => $appEnv === 'dev',
            'cache_dir' => __DIR__ . '/../var/doctrine/cache',
            'proxy_dir' => __DIR__ . '/../var/doctrine/proxy',
            'metadata_dirs' => [__DIR__ . '/../src/Entity'],
            'types' => [
                UuidType::NAME => UuidType::class,
            ],
            'connection' => [
                'dbname' => $_ENV['DATABASE_DBNAME'] ?? null,
                'user' => $_ENV['DATABASE_USER'] ?? null,
                'password' => $_ENV['DATABASE_PASSWORD'] ?? null,
                'host' => $_ENV['DATABASE_HOST'] ?? null,
                'port' => $_ENV['DATABASE_PORT'] ?? null,
                'driver' => $_ENV['DATABASE_DRIVER'] ?? null,
                'charset' => 'utf-8',
            ],
        ];

        $this->authUrl = $_ENV['AUTH_URL'];

        $this->s3Client = [
            'host' => 'http://minio:9000',
            'key' => 'admin',
            'secret' => '12345678',
        ];

        if ($this->appEnv === 'dev') {
            // Overrides for development mode
            $this->diCompilationPath = null;
        }
    }

    public function getAppEnv(): string
    {
        return $this->appEnv;
    }

    public function getLogger(): array
    {
        return $this->logger;
    }

    public function getDiCompilationPath(): ?string
    {
        return $this->diCompilationPath;
    }

    public function getDoctrine(): array
    {
        return $this->doctrine;
    }

    public function getAuthUrl(): string
    {
        return $this->authUrl;
    }

    public function getS3Client(): array
    {
        return $this->s3Client;
    }
}
