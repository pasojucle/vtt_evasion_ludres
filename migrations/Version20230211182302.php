<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230211182302 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride CHANGE level_types level_types LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE bike_ride_type ADD display_level TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('UPDATE `bike_ride_type` SET `display_level`= 0 WHERE `name` LIKE \'Rando adultes et ados (sans encadrement)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type DROP display_level');
        $this->addSql('ALTER TABLE bike_ride CHANGE level_types level_types LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
