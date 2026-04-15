<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260413162919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_ride_track (id INT AUTO_INCREMENT NOT NULL, bike_ride_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, filename VARCHAR(255) NOT NULL, thumbnail VARCHAR(255) NOT NULL, INDEX IDX_CF27CA3B8A96134D (bike_ride_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bike_ride_track ADD CONSTRAINT FK_CF27CA3B8A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_track DROP FOREIGN KEY FK_CF27CA3B8A96134D');
        $this->addSql('DROP TABLE bike_ride_track');
    }
}
