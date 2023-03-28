<?php

declare(strict_types = 1);

namespace Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20230328211534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create file table';
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     */
    public function up(Schema $schema): void
    {
        $table = $schema->createTable('file');
        $table->addColumn('id', Types::INTEGER, [
            'autoincrement' => true,
        ]);
        $table->addColumn('uuid', UuidType::NAME);
        $table->addColumn('storage_type', Types::STRING);
        $table->addColumn('path', Types::STRING);
        $table->addColumn('name', Types::STRING);
        $table->addColumn('original_name', Types::STRING);
        $table->addColumn('file_type', Types::STRING, [
            'notnull' => false,
        ]);
        $table->addColumn('is_private', Types::BOOLEAN);
        $table->addColumn('created_at', Types::DATETIMETZ_MUTABLE);
        $table->addColumn('updated_at', Types::DATETIMETZ_MUTABLE);
        $table->addColumn('deleted_at', Types::DATETIMETZ_MUTABLE, [
            'notnull' => false,
        ]);

        $table->setPrimaryKey(['id']);
    }

    /**
     * @param Schema $schema
     * @throws SchemaException
     */
    public function down(Schema $schema): void
    {
        $schema->dropTable('file');
    }
}
