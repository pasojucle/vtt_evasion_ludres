<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221223145821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE disease DROP FOREIGN KEY FK_F3B6AC1A08E947C');
        $this->addSql('ALTER TABLE disease DROP FOREIGN KEY FK_F3B6AC1310FF969');
        $this->addSql('ALTER TABLE health_question DROP FOREIGN KEY FK_5ED234E0A08E947C');
        $this->addSql('DROP TABLE disease_kind');
        $this->addSql('DROP TABLE disease');
        $this->addSql('DROP TABLE health_question');
        $this->addSql('ALTER TABLE health ADD at_least_one_positve_response TINYINT(0) DEFAULT 1 NOT NULL, DROP social_security_number, DROP mutual_company, DROP mutual_number, DROP blood_group, DROP tetanus_booster, DROP doctor_name, DROP doctor_address, DROP doctor_town, DROP doctor_phone');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE disease_kind (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, category INT NOT NULL, bike_ride_alert TINYINT(1) DEFAULT 0 NOT NULL, custom_label TINYINT(1) DEFAULT 0 NOT NULL, emergency_treatment TINYINT(1) DEFAULT 0 NOT NULL, deleted TINYINT(1) DEFAULT 0 NOT NULL, order_by INT NOT NULL, licence_category INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE disease (id INT AUTO_INCREMENT NOT NULL, health_id INT NOT NULL, disease_kind_id INT DEFAULT NULL, title VARCHAR(100) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, curent_treatment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, emergency_treatment LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_F3B6AC1310FF969 (disease_kind_id), INDEX IDX_F3B6AC1A08E947C (health_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE health_question (id INT AUTO_INCREMENT NOT NULL, health_id INT DEFAULT NULL, field INT NOT NULL, value TINYINT(1) DEFAULT NULL, INDEX IDX_5ED234E0A08E947C (health_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE disease ADD CONSTRAINT FK_F3B6AC1A08E947C FOREIGN KEY (health_id) REFERENCES health (id)');
        $this->addSql('ALTER TABLE disease ADD CONSTRAINT FK_F3B6AC1310FF969 FOREIGN KEY (disease_kind_id) REFERENCES disease_kind (id)');
        $this->addSql('ALTER TABLE health_question ADD CONSTRAINT FK_5ED234E0A08E947C FOREIGN KEY (health_id) REFERENCES health (id)');
        $this->addSql('ALTER TABLE health ADD social_security_number VARCHAR(15) DEFAULT NULL, ADD mutual_company VARCHAR(100) DEFAULT NULL, ADD mutual_number VARCHAR(25) DEFAULT NULL, ADD blood_group VARCHAR(5) DEFAULT NULL, ADD tetanus_booster DATE DEFAULT NULL, ADD doctor_name VARCHAR(100) DEFAULT NULL, ADD doctor_address VARCHAR(255) DEFAULT NULL, ADD doctor_town VARCHAR(100) DEFAULT NULL, ADD doctor_phone VARCHAR(10) DEFAULT NULL, DROP at_least_one_positve_response');
    }
}
