<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221119131300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_ride_user (bike_ride_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_25973ADF8A96134D (bike_ride_id), INDEX IDX_25973ADFA76ED395 (user_id), PRIMARY KEY(bike_ride_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bike_ride_level (bike_ride_id INT NOT NULL, level_id INT NOT NULL, INDEX IDX_832EFE8E8A96134D (bike_ride_id), INDEX IDX_832EFE8E5FB14BA7 (level_id), PRIMARY KEY(bike_ride_id, level_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bike_ride_user ADD CONSTRAINT FK_25973ADF8A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_user ADD CONSTRAINT FK_25973ADFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_level ADD CONSTRAINT FK_832EFE8E8A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_level ADD CONSTRAINT FK_832EFE8E5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride ADD level_types LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('UPDATE `bike_ride` SET `level_types`=\'[]\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_user DROP FOREIGN KEY FK_25973ADF8A96134D');
        $this->addSql('ALTER TABLE bike_ride_user DROP FOREIGN KEY FK_25973ADFA76ED395');
        $this->addSql('ALTER TABLE bike_ride_level DROP FOREIGN KEY FK_832EFE8E8A96134D');
        $this->addSql('ALTER TABLE bike_ride_level DROP FOREIGN KEY FK_832EFE8E5FB14BA7');
        $this->addSql('DROP TABLE bike_ride_user');
        $this->addSql('DROP TABLE bike_ride_level');
        $this->addSql('ALTER TABLE bike_ride DROP level_types');
    }
}
