#!/usr/bin/env php
<?php

declare(strict_types = 1);

use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Console\EntityManagerProvider\SingleManagerProvider;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as ConsoleRunnerMigrations;

$container = require __DIR__ . '/../config/bootstrap.php';
$entityManager = $container->get(EntityManager::class);
$logger = $container->get(LoggerInterface::class);
$dependencyFactory = DependencyFactory::fromEntityManager(new PhpFile('config/migrations.php'), new ExistingEntityManager($entityManager));

$cli = new Application('Console commands');

// ORM and dbal commands
ConsoleRunner::addCommands($cli, new SingleManagerProvider($entityManager));

// Migration commands
// https://www.doctrine-project.org/projects/doctrine-migrations/en/3.5/reference/custom-integration.html#custom-integration
ConsoleRunnerMigrations::addCommands($cli, $dependencyFactory);

$cli->run();
