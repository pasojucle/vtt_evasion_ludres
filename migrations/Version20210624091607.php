<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210624091607 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id INT AUTO_INCREMENT NOT NULL, street VARCHAR(255) NOT NULL, postal_code VARCHAR(5) NOT NULL, town VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE approval (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, type INT NOT NULL, value TINYINT(1) DEFAULT NULL, INDEX IDX_16E0952BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE disease (id INT AUTO_INCREMENT NOT NULL, health_id INT NOT NULL, type INT NOT NULL, title VARCHAR(100) DEFAULT NULL, curent_treatment LONGTEXT DEFAULT NULL, emergency_treatment LONGTEXT DEFAULT NULL, label INT NOT NULL, INDEX IDX_F3B6AC1A08E947C (health_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE health (id INT AUTO_INCREMENT NOT NULL, social_security_number VARCHAR(15) DEFAULT NULL, mutual_company VARCHAR(100) DEFAULT NULL, mutual_number VARCHAR(25) DEFAULT NULL, blood_group VARCHAR(5) DEFAULT NULL, tetanus_booster DATE DEFAULT NULL, doctor_name VARCHAR(100) DEFAULT NULL, doctor_address VARCHAR(255) DEFAULT NULL, doctor_town VARCHAR(100) DEFAULT NULL, doctor_phone VARCHAR(10) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE health_question (id INT AUTO_INCREMENT NOT NULL, health_id INT NOT NULL, field INT NOT NULL, value TINYINT(1) DEFAULT NULL, INDEX IDX_5ED234E0A08E947C (health_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE identity (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, address_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, birth_date DATE DEFAULT NULL, birthplace VARCHAR(100) DEFAULT NULL, phone VARCHAR(10) DEFAULT NULL, mobile VARCHAR(10) DEFAULT NULL, profession VARCHAR(100) DEFAULT NULL, kinship INT DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, picture VARCHAR(100) DEFAULT NULL, INDEX IDX_6A95E9C4A76ED395 (user_id), INDEX IDX_6A95E9C4F5B7AF75 (address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licence (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, type INT DEFAULT NULL, coverage INT DEFAULT NULL, magazine_subscription TINYINT(1) NOT NULL, subscription_amount DOUBLE PRECISION DEFAULT NULL, valid TINYINT(1) NOT NULL, additional_family_member TINYINT(1) NOT NULL, medical_certificate_required TINYINT(1) NOT NULL, category INT NOT NULL, testing TINYINT(1) NOT NULL, season INT NOT NULL, INDEX IDX_1DAAE648A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membership_fee (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, additional_family_member TINYINT(1) DEFAULT NULL, new_member TINYINT(1) DEFAULT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE membership_fee_amount (id INT AUTO_INCREMENT NOT NULL, membership_fee_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, coverage INT DEFAULT NULL, INDEX IDX_B970880DD5F81F53 (membership_fee_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_step (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, filename VARCHAR(255) DEFAULT NULL, form INT DEFAULT NULL, order_by INT NOT NULL, content LONGTEXT DEFAULT NULL, category INT DEFAULT NULL, testing TINYINT(1) NOT NULL, to_pdf TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, health_id INT DEFAULT NULL, licence_number VARCHAR(25) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_8D93D64940BE3323 (licence_number), UNIQUE INDEX UNIQ_8D93D649A08E947C (health_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE approval ADD CONSTRAINT FK_16E0952BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE disease ADD CONSTRAINT FK_F3B6AC1A08E947C FOREIGN KEY (health_id) REFERENCES health (id)');
        $this->addSql('ALTER TABLE health_question ADD CONSTRAINT FK_5ED234E0A08E947C FOREIGN KEY (health_id) REFERENCES health (id)');
        $this->addSql('ALTER TABLE identity ADD CONSTRAINT FK_6A95E9C4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE identity ADD CONSTRAINT FK_6A95E9C4F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id)');
        $this->addSql('ALTER TABLE licence ADD CONSTRAINT FK_1DAAE648A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE membership_fee_amount ADD CONSTRAINT FK_B970880DD5F81F53 FOREIGN KEY (membership_fee_id) REFERENCES membership_fee (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649A08E947C FOREIGN KEY (health_id) REFERENCES health (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity DROP FOREIGN KEY FK_6A95E9C4F5B7AF75');
        $this->addSql('ALTER TABLE disease DROP FOREIGN KEY FK_F3B6AC1A08E947C');
        $this->addSql('ALTER TABLE health_question DROP FOREIGN KEY FK_5ED234E0A08E947C');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649A08E947C');
        $this->addSql('ALTER TABLE membership_fee_amount DROP FOREIGN KEY FK_B970880DD5F81F53');
        $this->addSql('ALTER TABLE approval DROP FOREIGN KEY FK_16E0952BA76ED395');
        $this->addSql('ALTER TABLE identity DROP FOREIGN KEY FK_6A95E9C4A76ED395');
        $this->addSql('ALTER TABLE licence DROP FOREIGN KEY FK_1DAAE648A76ED395');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE approval');
        $this->addSql('DROP TABLE disease');
        $this->addSql('DROP TABLE health');
        $this->addSql('DROP TABLE health_question');
        $this->addSql('DROP TABLE identity');
        $this->addSql('DROP TABLE licence');
        $this->addSql('DROP TABLE membership_fee');
        $this->addSql('DROP TABLE membership_fee_amount');
        $this->addSql('DROP TABLE registration_step');
        $this->addSql('DROP TABLE user');
    }
}
