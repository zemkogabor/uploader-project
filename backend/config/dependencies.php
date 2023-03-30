<?php

declare(strict_types = 1);

use App\Settings;
use Aws\S3\S3Client;
use DI\ContainerBuilder;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

return static function (ContainerBuilder $containerBuilder, Settings $settings, LoggerInterface $logger) {
    $containerBuilder->addDefinitions([
        Settings::class => $settings,
        LoggerInterface::class => $logger,
        EntityManager::class => function (ContainerInterface $container) {
            /**
             * @var Settings $settings
             */
            $settings = $container->get(Settings::class);
            $settingsDoctrine = $settings->getDoctrine();
            $cache = $settingsDoctrine['dev_mode'] ?
                new ArrayAdapter() :
                new FilesystemAdapter(directory: $settingsDoctrine['cache_dir']);

            $config = ORMSetup::createAttributeMetadataConfiguration(
                $settingsDoctrine['metadata_dirs'],
                $settingsDoctrine['dev_mode'],
                $settingsDoctrine['proxy_dir'],
                $cache
            );

            foreach ($settingsDoctrine['types'] as $dbType => $doctrineType) {
                Type::addType($dbType, $doctrineType);
            }

            return new EntityManager(DriverManager::getConnection($settingsDoctrine['connection']), $config);
        },
        S3Client::class => function(ContainerInterface $container) {
            /**
             * @var Settings $settings
             */
            $settings = $container->get(Settings::class);
            $settingsS3Client = $settings->getS3Client();

            return new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1',
                'endpoint' => $settingsS3Client['host'],
                'credentials' => [
                    'key' => $settingsS3Client['key'],
                    'secret' => $settingsS3Client['secret'],
                ],
                // Without this got this error: "Could not resolve host: original.minio"
                // It would use the bucket as a subdomain, which does not work in the docker network environment.
                // https://github.com/agentejo/CloudStorage/issues/2#issuecomment-400954602
                'use_path_style_endpoint' => true,
            ]);
        }
    ]);
};
