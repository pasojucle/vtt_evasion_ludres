<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230501053816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD closing_duration INT DEFAULT NULL');
        $this->addSql('UPDATE `bike_ride_type` SET `closing_duration`= 2 WHERE `registration` > 0');
        $this->addSql('UPDATE `bike_ride_type` SET `closing_duration`= 0 WHERE `use_levels` = 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type DROP closing_duration');
    }
}
