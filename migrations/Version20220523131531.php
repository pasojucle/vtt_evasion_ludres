<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220523131531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE respondent (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_409B5150B3FE509D (survey_id), INDEX IDX_409B5150A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO respondent SELECT id, survey_id, user_id, created_at FROM survey_user');
        $this->addSql('ALTER TABLE respondent ADD CONSTRAINT FK_409B5150B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE respondent ADD CONSTRAINT FK_409B5150A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey ADD bike_ride_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC8A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AD5F9BFC8A96134D ON survey (bike_ride_id)');
        $this->addSql('ALTER TABLE survey_user MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD682A76ED395');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD682B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE survey_user DROP id, DROP created_at');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE survey_user ADD PRIMARY KEY (survey_id, user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE survey_user');
        $this->addSql('CREATE TABLE survey_user (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4B7AD682B3FE509D (survey_id), INDEX IDX_4B7AD682A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('INSERT INTO survey_user SELECT id, survey_id, user_id, created_at FROM respondent');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE respondent');
        $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFC8A96134D');
        $this->addSql('DROP INDEX IDX_AD5F9BFC8A96134D ON survey');
        $this->addSql('ALTER TABLE survey DROP bike_ride_id');
    }
}
