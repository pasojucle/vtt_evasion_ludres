<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260620050226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand ADD state VARCHAR(80) DEFAULT \'draft\' NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET state = 'expired' WHERE valided_at IS NOT NULL AND valided_at < NOW() - INTERVAL 30 DAY AND disabled=1 AND deleted=0
        SQL);
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET state = 'published' WHERE valided_at IS NOT NULL AND disabled=0 AND deleted=0
        SQL);
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET state = 'archived' WHERE deleted=1
        SQL);
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET state = 'draft' WHERE valided_at IS NULL AND deleted=0 AND disabled=0
        SQL);
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE second_hand DROP deleted, DROP disabled
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand ADD deleted TINYINT(1) NOT NULL, ADD disabled TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function postDown(Schema $schema): void
    {
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET disabled=1, deleted=0 WHERE  state = 'expired'
        SQL);
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET disabled=0, deleted=0 WHERE state = 'published'
        SQL);
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET deleted=1 WHERE state = 'archived'
        SQL);
        $this->connection->executeQuery(<<<SQL
            UPDATE second_hand SET deleted=0, disabled=0 WHERE state = 'draft' 
        SQL);
        $this->connection->executeQuery(<<<SQL
            ALTER TABLE second_hand DROP state
        SQL);
    }
}
