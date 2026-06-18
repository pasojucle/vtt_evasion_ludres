<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260523171306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level ADD type_enum VARCHAR(255) DEFAULT \'adult\' NOT NULL');
        $this->addSql('ALTER TABLE message ADD level_type_enum VARCHAR(255) DEFAULT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getTypes() as $type) {
            $this->connection->executeQuery('UPDATE level SET type_enum=:enum WHERE type=:type', $type);
            $this->connection->executeQuery('UPDATE message SET level_type_enum=:enum WHERE level_type=:type', $type);
        }
        $this->connection->executeQuery('ALTER TABLE level DROP type');
        $this->connection->executeQuery('ALTER TABLE level CHANGE type_enum type VARCHAR(255) DEFAULT \'school\' NOT NULL');
        $this->connection->executeQuery('ALTER TABLE message DROP level_type');
        $this->connection->executeQuery('ALTER TABLE message CHANGE level_type_enum level_type VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level ADD type_const INT NOT NULL');
        $this->addSql('ALTER TABLE message Add level_type_const INT DEFAULT NULL');
    }

    public function postDown(Schema $schema): void
    {
        foreach($this->getTypes() as $type) {
            $this->connection->executeQuery('UPDATE level SET type_const=:type WHERE type=:enum', $type);
            $this->connection->executeQuery('UPDATE message SET level_type_const=:type WHERE level_type=:enum', $type);
        }
        $this->connection->executeQuery('ALTER TABLE level DROP type');
        $this->connection->executeQuery('ALTER TABLE level CHANGE type_const type INT NOT NULL');
        $this->connection->executeQuery('ALTER TABLE message DROP level_type');
        $this->connection->executeQuery('ALTER TABLE level CHANGE level_type_const level_type INT DEFAULT NULL');
    }

    private function getTypes(): array
    {
        return [
            ['type' => '1', 'enum' => 'school'],
            ['type' => '2', 'enum' => 'frame'],
            ['type' => '3', 'enum' => 'adult'],
        ];
    }
}
