<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230422170034 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD need_framers TINYINT(1) DEFAULT \'0\' NOT NULL, DROP cluster_choice');
        $this->addSql('UPDATE `bike_ride_type` SET `need_framers`=1 WHERE `registration` = 1');
        $this->addSql('UPDATE `bike_ride_type` SET `need_framers`=1 WHERE `name`="Activité fédérale"');
        $this->addSql('UPDATE `session` AS s INNER JOIN cluster AS c ON c.id = s.cluster_id INNER JOIN bike_ride AS b ON b.id = c.bike_ride_id INNER JOIN bike_ride_type AS bt oN b.bike_ride_type_id = bt.id INNER JOIN user AS u ON s.user_id = u.id SET `availability` = 1 WHERE bt.need_framers = 1 AND bt.registration = 2 AND c.role = "ROLE_FRAME" AND u.roles LIKE "%ROLE_FRAME%"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type ADD cluster_choice TINYINT(1) DEFAULT 0 NOT NULL, DROP need_framers');
    }
}
