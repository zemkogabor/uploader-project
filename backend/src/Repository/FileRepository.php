<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\FileEntity;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\Mapping\ClassMetadata;
use Psr\Log\LoggerInterface;

/**
 * @method FileEntity findOneBy(array $criteria, array $orderBy = null)
 * @method FileEntity[] findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
 */
class FileRepository extends EntityRepository
{
    public function __construct(protected LoggerInterface $logger, protected EntityManager $em)
    {
        parent::__construct($em, new ClassMetadata(FileEntity::class));
    }

    /**
     * @param FileEntity $file
     * @return FileEntity
     * @throws ORMException
     */
    public function createFile(FileEntity $file): FileEntity
    {
        $this->em->persist($file);
        $this->em->flush();

        return $file;
    }
}
