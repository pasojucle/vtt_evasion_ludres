<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260728161337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE licence ADD bike_type VARCHAR(255) DEFAULT \'muscular\' NOT NULL, ADD coverage_enum VARCHAR(255) DEFAULT \'undefined\' NOT NULL');
        $this->addSql('ALTER TABLE membership_fee_amount ADD coverage_enum VARCHAR(255) DEFAULT \'undefined\' NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        foreach($this->getBikeTypes() as $bikeType) {
            $this->connection->executeQuery('UPDATE licence SET bike_type=:enum WHERE is_vae=:value', $bikeType);
        }
    
        foreach($this->getCoverages() as $coverage) {
            $this->connection->executeQuery('UPDATE licence SET coverage_enum=:enum WHERE coverage=:const', $coverage);
            $this->connection->executeQuery('UPDATE membership_fee_amount SET coverage_enum=:enum WHERE coverage=:const', $coverage);
        }

        $this->connection->executeQuery('ALTER TABLE licence DROP is_vae, DROP coverage, CHANGE coverage_enum coverage VARCHAR(255) DEFAULT \'undefined\' NOT NULL');
        $this->connection->executeQuery('ALTER TABLE membership_fee_amount DROP coverage, CHANGE coverage_enum coverage VARCHAR(255) DEFAULT \'undefined\' NOT NULL');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD is_vae TINYINT(1) DEFAULT 0 NOT NULL, ADD coverage_const INT DEFAULT NULL');
        $this->addSql('ALTER TABLE membership_fee_amount CHANGE coverage_cont coverage INT DEFAULT NULL');
    }

    public function postDown(Schema $schema): void
    {
        foreach($this->getBikeTypes() as $bikeType) {
            $this->connection->executeQuery('UPDATE licence SET is_vae=:value WHERE bike_type=:enum', $bikeType);
        }

        foreach($this->getCoverages() as $coverage) {
            $this->connection->executeQuery('UPDATE licence SET coverage=:const WHERE coverage_enum=:enum', $coverage);
            $this->connection->executeQuery('UPDATE membership_fee_amount SET coverage=:const WHERE coverage_enum=:enum', $coverage);
        }
        $this->connection->executeQuery('ALTER TABLE licence ADD is_vae TINYINT(1) DEFAULT 0 NOT NULL, DROP coverage, CHANGE coverage_const coverage INT DEFAULT NULL');
        $this->connection->executeQuery('ALTER TABLE membership_fee_amount DROP coverage, CHANGE coverage_const coverage INT DEFAULT NULL');
    }

    private function getBikeTypes(): array
    {
        return [
            ['enum' => 'muscular', 'value' => 0],
            ['enum' => 'electric', 'value' => 1],
        ];
    }

    private function getCoverages(): array
    {
        return [
            ['enum' => 'mini_gear', 'const' => 1],
            ['enum' => 'small_gear', 'const' => 2],
            ['enum' => 'high_gear', 'const' => 3],
        ];
    }
}
