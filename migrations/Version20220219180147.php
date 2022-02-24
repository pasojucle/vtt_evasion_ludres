<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220219180147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_ride (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) NOT NULL, content LONGTEXT DEFAULT NULL, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, display_duration INT NOT NULL, min_age INT DEFAULT NULL, type INT NOT NULL, closing_duration INT DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO bike_ride SELECT * FROM `event`');
        $this->addSql('ALTER TABLE cluster ADD bike_ride_id INT NOT NULL');
        $this->addSql('UPDATE cluster SET bike_ride_id=`event_id`');
        $this->addSql('ALTER TABLE cluster ADD CONSTRAINT FK_E5C569948A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id)');
        $this->addSql('CREATE INDEX IDX_E5C569948A96134D ON cluster (bike_ride_id)');
        
        $this->addSql('ALTER TABLE cluster DROP FOREIGN KEY FK_E5C5699471F7E88B');
        $this->addSql('DROP INDEX IDX_E5C5699471F7E88B ON cluster');
        $this->addSql('DROP TABLE event');
        $this->addSql('ALTER TABLE cluster DROP event_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster DROP FOREIGN KEY FK_E5C569948A96134D');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, start_at DATETIME NOT NULL, end_at DATETIME DEFAULT NULL, display_duration INT NOT NULL, min_age INT DEFAULT NULL, type INT NOT NULL, closing_duration INT DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP TABLE bike_ride');
        $this->addSql('DROP INDEX IDX_E5C569948A96134D ON cluster');
        $this->addSql('ALTER TABLE cluster CHANGE bike_ride_id event_id INT NOT NULL');
        $this->addSql('ALTER TABLE cluster ADD CONSTRAINT FK_E5C5699471F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_E5C5699471F7E88B ON cluster (event_id)');
    }
}
