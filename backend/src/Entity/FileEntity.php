<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Repository\FileRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: FileRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'file')]
class FileEntity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public int $id;

    #[ORM\Column(type: 'uuid', unique: true)]
    public UuidInterface|string $uuid;

    #[ORM\Column]
    public string $bucket;

    #[ORM\Column]
    public string $key;

    #[ORM\Column]
    public string $filename;

    #[ORM\Column]
    public bool $is_private;

    #[ORM\Column]
    public DateTimeImmutable $created_at;

    #[ORM\Column]
    public DateTimeImmutable $updated_at;

    #[ORM\Column]
    public DateTimeImmutable $deleted_at;

    /** @noinspection PhpUnused */
    #[ORM\PrePersist]
    public function setUuid(): void
    {
        $this->uuid = Uuid::uuid4();
    }

    /** @noinspection PhpUnused */
    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->created_at = new DateTimeImmutable();
    }

    /** @noinspection PhpUnused */
    #[ORM\PreUpdate]
    #[ORM\PrePersist]
    public function setUpdatedAt(): void
    {
        $this->updated_at = new DateTimeImmutable();
    }

    /** @noinspection PhpUnused */
    #[ORM\PreRemove]
    public function setDeletedAt(): void
    {
        $this->deleted_at = new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getS3Path(): string
    {
        return 's3://' . $this->bucket . '/' . $this->key;
    }
}
