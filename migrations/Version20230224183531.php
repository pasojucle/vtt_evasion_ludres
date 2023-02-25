<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230224183531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD show_member_list TINYINT(1) DEFAULT 0 NOT NULL, ADD use_levels TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('UPDATE bike_ride_type SET use_levels = display_level');
        $this->addSql('ALTER TABLE bike_ride_type DROP display_level');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type DROP show_member_list, CHANGE use_levels display_level TINYINT(1) DEFAULT 1 NOT NULL');
    }
}
