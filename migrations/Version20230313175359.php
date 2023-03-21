<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230313175359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD clusters LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', ADD registration INT DEFAULT 0 NOT NULL, ADD cluster_choice TINYINT(1) DEFAULT 0 NOT NULL');

        $this->addSql('UPDATE `bike_ride_type` SET `clusters`=\'{"0":"Groupe 1"}\', `registration`=2 WHERE `is_school`= 0');
        $this->addSql('UPDATE `bike_ride_type` SET `clusters`=\'{"0":"Groupe 1 (avions de chasse),","1":"Groupe 2 (randonneurs avertis)","2":"Groupe 3 (debutants. remise en forme)."}\', `registration`=2,	`show_member_list`=1 WHERE `use_levels`= 0');
        $this->addSql('UPDATE `bike_ride_type` SET `clusters`=\'[]\', `registration`=1 WHERE `is_school`= 1');
        $this->addSql('UPDATE `bike_ride_type` SET `clusters`=\'[]\', `registration`=0  WHERE `is_registrable`= 0');

        $this->addSql('ALTER TABLE bike_ride_type DROP is_registrable, DROP is_school');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD is_registrable TINYINT(1) DEFAULT 1 NOT NULL, ADD is_school TINYINT(1) DEFAULT 0 NOT NULL, DROP clusters, DROP registration, DROP cluster_choice');

    }
}
