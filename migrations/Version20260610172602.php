<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610172602 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey_issue ADD response_type_enum VARCHAR(255) DEFAULT \'choice\' NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getTypes() as $type) {
            $this->connection->executeQuery('UPDATE survey_issue SET response_type_enum=:enum WHERE response_type=:type', $type);
        }
        $this->connection->executeQuery('ALTER TABLE survey_issue DROP response_type');
        $this->connection->executeQuery('ALTER TABLE survey_issue CHANGE response_type_enum response_type VARCHAR(255) DEFAULT \'choice\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey_issue ADD response_type_const INT NOT NULL');
    }

    public function postDown(Schema $schema): void
    {
        foreach($this->getTypes() as $type) {
            $this->connection->executeQuery('UPDATE survey_issue SET response_type_const=:type WHERE response_type=:enum', $type);
        }
        $this->connection->executeQuery('ALTER TABLE survey_issue DROP response_type');
        $this->connection->executeQuery('ALTER TABLE survey_issue CHANGE response_type_const response_type INT NOT NULL');
    }

    private function getTypes(): array
    {
        return [
            ['type' => '1', 'enum' => 'text'],
            ['type' => '2', 'enum' => 'choice'],
            ['type' => '3', 'enum' => 'check'],
        ];
    }
}
