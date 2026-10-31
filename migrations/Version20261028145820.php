<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261028145820 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agreement ADD disabled_by_id INT DEFAULT NULL, ADD disabled_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE agreement ADD CONSTRAINT FK_2E655A241688BE50 FOREIGN KEY (disabled_by_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_2E655A241688BE50 ON agreement (disabled_by_id)');
    }


    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE agreement SET disabled_at=NOW() WHERE enabled=0');
        $this->connection->executeQuery('ALTER TABLE agreement DROP enabled');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE agreement DROP FOREIGN KEY FK_2E655A241688BE50');
        $this->addSql('DROP INDEX IDX_2E655A241688BE50 ON agreement');
        $this->addSql('ALTER TABLE agreement ADD enabled TINYINT(1) DEFAULT 1 NOT NULL, DROP disabled_by_id');
    }
    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE agreement SET enabled=0 WHERE disabled_at IS NOT NULL');
        $this->connection->executeQuery('ALTER TABLE agreement DROP disabled_at');
    }
}
