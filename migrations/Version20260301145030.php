<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260301145030 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type CHANGE require_disponibility require_availability TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE session CHANGE availability availability ENUM(\'registered\', \'available\', \'unavailable\', \'none\') DEFAULT NULL COMMENT \'(DC2Type:Availability)\'');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE session SET availability=:availability WHERE availability IS NULL', ['availability' => 'none']);
        $this->connection->executeQuery('ALTER TABLE session CHANGE availability availability ENUM(\'registered\', \'available\', \'unavailable\', \'none\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:Availability)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE session CHANGE availability availability ENUM(\'registered\', \'available\', \'unavailable\') DEFAULT NULL COMMENT \'(DC2Type:Availability)\'');
        $this->addSql('ALTER TABLE bike_ride_type CHANGE require_availability require_disponibility TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
