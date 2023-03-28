<?php

declare(strict_types = 1);

use App\Settings;
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
    ]);
};
