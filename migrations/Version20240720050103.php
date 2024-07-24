<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240720050103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD registration_tmp ENUM(\'none\', \'school\', \'cluster\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:registration_enum)\'');    
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = \'none\' WHERE registration = 0');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = \'school\' WHERE registration = 1');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = \'cluster\' WHERE registration = 2');
        $this->addSql('ALTER TABLE bike_ride_type DROP registration');
        $this->addSql('ALTER TABLE bike_ride_type CHANGE registration_tmp registration ENUM(\'none\', \'school\', \'cluster\') DEFAULT \'none\' NOT NULL COMMENT \'(DC2Type:registration_enum)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD registration_tmp INT DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = 0 WHERE registration = \'none\'');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = 1 WHERE registration = \'school\' ');
        $this->addSql('UPDATE bike_ride_type SET registration_tmp = 2 WHERE registration = \'cluster\'');
        $this->addSql('ALTER TABLE bike_ride_type DROP registration');
        $this->addSql('ALTER TABLE bike_ride_type CHANGE registration_tmp registration INT DEFAULT 0 NOT NULL');
    }
}
