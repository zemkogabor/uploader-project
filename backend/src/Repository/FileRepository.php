<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\FileEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Psr\Log\LoggerInterface;

class FileRepository extends EntityRepository
{
    public function __construct(protected LoggerInterface $logger, protected EntityManager $em)
    {
        parent::__construct($em, new ClassMetadata(FileEntity::class));
    }
}
