<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230927161608 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride ADD level_filter LONGTEXT DEFAULT \'[]\' NOT NULL COMMENT \'(DC2Type:json)\'');
        $bikeRideLevels = $this->connection->executeQuery('SELECT * FROM `bike_ride_level` WHERE 1')->fetchAllAssociative();
        $bikeRides = $this->connection->executeQuery('SELECT * FROM `bike_ride` WHERE `level_types` NOT LIKE \'[]\'')->fetchAllAssociative();

        $levelsByBikeRide = [];

        foreach($bikeRides as $bikeRide) {
            $levelsByBikeRide[$bikeRide['id']] = json_decode($bikeRide['level_types']);
        }
        foreach($bikeRideLevels as $bikeRideLevel) {
            $levelsByBikeRide[$bikeRideLevel['bike_ride_id']][] = $bikeRideLevel['level_id'];
        }

        foreach($levelsByBikeRide as $bikeRideId => $levels) {
            $this->addSql('UPDATE `bike_ride` SET `level_filter`=:levels WHERE `id`=:id', ['id'=> $bikeRideId, 'levels' => json_encode($levels)]);
        }
        $this->addSql('ALTER TABLE bike_ride_level DROP FOREIGN KEY FK_832EFE8E8A96134D');
        $this->addSql('ALTER TABLE bike_ride_level DROP FOREIGN KEY FK_832EFE8E5FB14BA7');
        $this->addSql('DROP TABLE bike_ride_level');
        $this->addSql('ALTER TABLE bike_ride DROP level_types');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride DROP level_filter');
        $this->addSql('CREATE TABLE bike_ride_level (bike_ride_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_832EFE8E5FB14BA7 (level_id), INDEX IDX_832EFE8E8A96134D (bike_ride_id), PRIMARY KEY(bike_ride_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE bike_ride_level ADD CONSTRAINT FK_832EFE8E8A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_level ADD CONSTRAINT FK_832EFE8E5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride ADD level_types LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
