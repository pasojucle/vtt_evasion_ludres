<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250927093845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD testing_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE licence set testing_at = created_at, created_at = NULL WHERE final = 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence DROP testing_at');
    }

    public function preDown(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE licence set created_at = testing_at WHERE final = 0 AND testing_at IS NOT NULL');
    }
}
