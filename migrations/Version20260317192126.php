<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260317192126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_ride_member (bike_ride_id INT NOT NULL, member_id INT NOT NULL, INDEX IDX_FE43D1B38A96134D (bike_ride_id), INDEX IDX_FE43D1B37597D3FE (member_id), PRIMARY KEY(bike_ride_id, member_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_gardian (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, identity_id INT DEFAULT NULL, kinship ENUM(\'father\', \'mother\', \'guardianship\', \'other\') NOT NULL COMMENT \'(DC2Type:Kinship)\', kind ENUM(\'legal_gardian\', \'second_contact\') NOT NULL COMMENT \'(DC2Type:GardianKind)\', INDEX IDX_99DF3937597D3FE (member_id), INDEX IDX_99DF393FF3ED4A8 (identity_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_permission (permission ENUM(\'bike_ride_cluster\', \'bike_ride\', \'user\', \'product\', \'survey\', \'notification\', \'second_hand\', \'permission\', \'documentation\', \'slideshow\', \'participation\', \'summary\', \'skill\') NOT NULL COMMENT \'(DC2Type:Permission)\', member_id INT NOT NULL, INDEX IDX_B078C437597D3FE (member_id), PRIMARY KEY(member_id, permission)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE member_skill (id INT AUTO_INCREMENT NOT NULL, member_id INT DEFAULT NULL, skill_id INT DEFAULT NULL, evaluate_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', evaluation ENUM(\'unacquired\', \'pending\', \'acquired\') NOT NULL COMMENT \'(DC2Type:Evaluation)\', INDEX IDX_3C1C71847597D3FE (member_id), INDEX IDX_3C1C71845585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_member (survey_id INT NOT NULL, member_id INT NOT NULL, INDEX IDX_58EA3214B3FE509D (survey_id), INDEX IDX_58EA32147597D3FE (member_id), PRIMARY KEY(survey_id, member_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bike_ride_member ADD CONSTRAINT FK_FE43D1B38A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_member ADD CONSTRAINT FK_FE43D1B37597D3FE FOREIGN KEY (member_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE member_gardian ADD CONSTRAINT FK_99DF3937597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE member_gardian ADD CONSTRAINT FK_99DF393FF3ED4A8 FOREIGN KEY (identity_id) REFERENCES identity (id)');
        $this->addSql('ALTER TABLE member_permission ADD CONSTRAINT FK_B078C437597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE member_skill ADD CONSTRAINT FK_3C1C71847597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE member_skill ADD CONSTRAINT FK_3C1C71845585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE survey_member ADD CONSTRAINT FK_58EA3214B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE survey_member ADD CONSTRAINT FK_58EA32147597D3FE FOREIGN KEY (member_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_gardian DROP FOREIGN KEY FK_EE645A0DA76ED395');
        $this->addSql('ALTER TABLE user_gardian DROP FOREIGN KEY FK_EE645A0DFF3ED4A8');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD682B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD682A76ED395');
        $this->addSql('ALTER TABLE bike_ride_user DROP FOREIGN KEY FK_25973ADF8A96134D');
        $this->addSql('ALTER TABLE bike_ride_user DROP FOREIGN KEY FK_25973ADFA76ED395');
        $this->addSql('ALTER TABLE user_permission DROP FOREIGN KEY FK_472E5446A76ED395');
        $this->addSql('ALTER TABLE user_skill DROP FOREIGN KEY FK_BCFF1F2FA76ED395');
        $this->addSql('ALTER TABLE user_skill DROP FOREIGN KEY FK_BCFF1F2F5585C142');
        $this->addSql('ALTER TABLE health DROP FOREIGN KEY FK_CEDA2313A76ED395');
        $this->addSql('DROP INDEX UNIQ_CEDA2313A76ED395 ON health');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704BA76ED395');
        $this->addSql('DROP INDEX IDX_27BA704BA76ED395 ON history');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C5A76ED395');
        $this->addSql('DROP INDEX IDX_8F3F68C5A76ED395 ON log');
        $this->addSql('ALTER TABLE order_header DROP FOREIGN KEY FK_ADFDB814A76ED395');
        $this->addSql('DROP INDEX IDX_ADFDB814A76ED395 ON order_header');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748AA76ED395');
        $this->addSql('DROP INDEX IDX_7CE748AA76ED395 ON reset_password_request');
        $this->addSql('ALTER TABLE respondent DROP FOREIGN KEY FK_409B5150A76ED395');
        $this->addSql('DROP INDEX IDX_409B5150A76ED395 ON respondent');
        $this->addSql('ALTER TABLE second_hand DROP FOREIGN KEY FK_A325FA21A76ED395');
        $this->addSql('DROP INDEX IDX_A325FA21A76ED395 ON second_hand');
        $this->addSql('ALTER TABLE survey_response DROP FOREIGN KEY FK_628C4DDCA76ED395');
        $this->addSql('DROP INDEX IDX_628C4DDCA76ED395 ON survey_response');
        $this->addSql('ALTER TABLE user ADD type VARCHAR(255) NOT NULL, ADD email VARCHAR(255) DEFAULT NULL, ADD token VARCHAR(64) DEFAULT NULL, ADD token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE licence_number licence_number VARCHAR(25) DEFAULT NULL, CHANGE password password VARCHAR(255) DEFAULT NULL, CHANGE active active TINYINT(1) DEFAULT NULL, CHANGE password_must_be_changed password_must_be_changed TINYINT(1) DEFAULT 0, CHANGE login_send login_send TINYINT(1) DEFAULT 0, CHANGE protected protected TINYINT(1) DEFAULT 0');
        $this->addSql('INSERT INTO member_gardian (`id`, `member_id`, `identity_id`, `kinship`, `kind`) SELECT `id`, `user_id`, `identity_id`, `kinship`, `kind` FROM user_gardian');
        $this->addSql('INSERT INTO `survey_member`(`survey_id`, `member_id`) SELECT `survey_id`, `user_id` FROM `survey_user`');
        $this->addSql('INSERT INTO `bike_ride_member`(`bike_ride_id`, `member_id`) SELECT `bike_ride_id`, `user_id` FROM bike_ride_user');
        $this->addSql('INSERT INTO `member_permission`(`permission`, `member_id`) SELECT `permission`, `user_id` FROM `user_permission`');
        $this->addSql('INSERT INTO `member_skill`(`id`, `member_id`, `skill_id`, `evaluate_at`, `evaluation`) SELECT `id`, `user_id`, `skill_id`, `evaluate_at`, `evaluation` FROM `user_skill`');
        $this->addSql('UPDATE `user` SET `type`=:type WHERE 1', ['type' => 'member']);
        $this->addSql('DROP TABLE user_gardian');
        $this->addSql('DROP TABLE survey_user');
        $this->addSql('DROP TABLE bike_ride_user');
        $this->addSql('DROP TABLE user_permission');
        $this->addSql('DROP TABLE user_skill');

        $this->addSql('ALTER TABLE health CHANGE user_id member_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE health ADD CONSTRAINT FK_CEDA23137597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEDA23137597D3FE ON health (member_id)');

        $this->addSql('ALTER TABLE history CHANGE user_id member_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704B7597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_27BA704B7597D3FE ON history (member_id)');

        $this->addSql('ALTER TABLE log CHANGE user_id member_id INT NOT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C57597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C57597D3FE ON log (member_id)');

        $this->addSql('ALTER TABLE order_header CHANGE user_id member_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_header ADD CONSTRAINT FK_ADFDB8147597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_ADFDB8147597D3FE ON order_header (member_id)');

        $this->addSql('ALTER TABLE reset_password_request CHANGE user_id member_id INT NOT NULL');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748A7597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7CE748A7597D3FE ON reset_password_request (member_id)');
;
        $this->addSql('ALTER TABLE respondent CHANGE user_id member_id INT NOT NULL');
        $this->addSql('ALTER TABLE respondent ADD CONSTRAINT FK_409B51507597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_409B51507597D3FE ON respondent (member_id)');
  
        $this->addSql('ALTER TABLE second_hand CHANGE user_id member_id INT NOT NULL');
        $this->addSql('ALTER TABLE second_hand ADD CONSTRAINT FK_A325FA217597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A325FA217597D3FE ON second_hand (member_id)');

        $this->addSql('ALTER TABLE survey_response CHANGE user_id member_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE survey_response ADD CONSTRAINT FK_628C4DDC7597D3FE FOREIGN KEY (member_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_628C4DDC7597D3FE ON survey_response (member_id)');
    }


    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_gardian (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, identity_id INT DEFAULT NULL, kinship ENUM(\'father\', \'mother\', \'guardianship\', \'other\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:Kinship)\', kind ENUM(\'legal_gardian\', \'second_contact\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:GardianKind)\', INDEX IDX_EE645A0DFF3ED4A8 (identity_id), INDEX IDX_EE645A0DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE survey_user (survey_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_4B7AD682B3FE509D (survey_id), INDEX IDX_4B7AD682A76ED395 (user_id), PRIMARY KEY(survey_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE bike_ride_user (bike_ride_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_25973ADF8A96134D (bike_ride_id), INDEX IDX_25973ADFA76ED395 (user_id), PRIMARY KEY(bike_ride_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_permission (permission ENUM(\'bike_ride_cluster\', \'bike_ride\', \'user\', \'product\', \'survey\', \'notification\', \'second_hand\', \'permission\', \'documentation\', \'slideshow\', \'participation\', \'summary\', \'skill\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:Permission)\', user_id INT NOT NULL, INDEX IDX_472E5446A76ED395 (user_id), PRIMARY KEY(user_id, permission)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user_skill (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, skill_id INT DEFAULT NULL, evaluate_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', evaluation ENUM(\'unacquired\', \'pending\', \'acquired\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:Evaluation)\', INDEX IDX_BCFF1F2FA76ED395 (user_id), INDEX IDX_BCFF1F2F5585C142 (skill_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE user_gardian ADD CONSTRAINT FK_EE645A0DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_gardian ADD CONSTRAINT FK_EE645A0DFF3ED4A8 FOREIGN KEY (identity_id) REFERENCES identity (id)');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_user ADD CONSTRAINT FK_25973ADF8A96134D FOREIGN KEY (bike_ride_id) REFERENCES bike_ride (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_user ADD CONSTRAINT FK_25973ADFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_permission ADD CONSTRAINT FK_472E5446A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2F5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id)');
        $this->addSql('ALTER TABLE bike_ride_member DROP FOREIGN KEY FK_FE43D1B38A96134D');
        $this->addSql('ALTER TABLE bike_ride_member DROP FOREIGN KEY FK_FE43D1B37597D3FE');
        $this->addSql('ALTER TABLE member_gardian DROP FOREIGN KEY FK_99DF3937597D3FE');
        $this->addSql('ALTER TABLE member_gardian DROP FOREIGN KEY FK_99DF393FF3ED4A8');
        $this->addSql('ALTER TABLE member_permission DROP FOREIGN KEY FK_B078C437597D3FE');
        $this->addSql('ALTER TABLE member_skill DROP FOREIGN KEY FK_3C1C71847597D3FE');
        $this->addSql('ALTER TABLE member_skill DROP FOREIGN KEY FK_3C1C71845585C142');
        $this->addSql('ALTER TABLE survey_member DROP FOREIGN KEY FK_58EA3214B3FE509D');
        $this->addSql('ALTER TABLE survey_member DROP FOREIGN KEY FK_58EA32147597D3FE');
        $this->addSql('DROP TABLE bike_ride_member');
        $this->addSql('DROP TABLE member_gardian');
        $this->addSql('DROP TABLE member_permission');
        $this->addSql('DROP TABLE member_skill');
        $this->addSql('DROP TABLE survey_member');
        $this->addSql('ALTER TABLE order_header DROP FOREIGN KEY FK_ADFDB8147597D3FE');
        $this->addSql('DROP INDEX IDX_ADFDB8147597D3FE ON order_header');
        $this->addSql('ALTER TABLE order_header CHANGE member_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_header ADD CONSTRAINT FK_ADFDB814A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_ADFDB814A76ED395 ON order_header (user_id)');
        $this->addSql('ALTER TABLE survey_response DROP FOREIGN KEY FK_628C4DDC7597D3FE');
        $this->addSql('DROP INDEX IDX_628C4DDC7597D3FE ON survey_response');
        $this->addSql('ALTER TABLE survey_response CHANGE member_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE survey_response ADD CONSTRAINT FK_628C4DDCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_628C4DDCA76ED395 ON survey_response (user_id)');
        $this->addSql('ALTER TABLE second_hand DROP FOREIGN KEY FK_A325FA217597D3FE');
        $this->addSql('DROP INDEX IDX_A325FA217597D3FE ON second_hand');
        $this->addSql('ALTER TABLE second_hand CHANGE member_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE second_hand ADD CONSTRAINT FK_A325FA21A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_A325FA21A76ED395 ON second_hand (user_id)');
        $this->addSql('ALTER TABLE health DROP FOREIGN KEY FK_CEDA23137597D3FE');
        $this->addSql('DROP INDEX UNIQ_CEDA23137597D3FE ON health');
        $this->addSql('ALTER TABLE health CHANGE member_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE health ADD CONSTRAINT FK_CEDA2313A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CEDA2313A76ED395 ON health (user_id)');
        $this->addSql('ALTER TABLE log DROP FOREIGN KEY FK_8F3F68C57597D3FE');
        $this->addSql('DROP INDEX IDX_8F3F68C57597D3FE ON log');
        $this->addSql('ALTER TABLE log CHANGE member_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE log ADD CONSTRAINT FK_8F3F68C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8F3F68C5A76ED395 ON log (user_id)');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748A7597D3FE');
        $this->addSql('DROP INDEX IDX_7CE748A7597D3FE ON reset_password_request');
        $this->addSql('ALTER TABLE reset_password_request CHANGE member_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7CE748AA76ED395 ON reset_password_request (user_id)');
        $this->addSql('ALTER TABLE user DROP type, DROP email, DROP token, DROP token_expires_at, CHANGE licence_number licence_number VARCHAR(25) NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE active active TINYINT(1) NOT NULL, CHANGE password_must_be_changed password_must_be_changed TINYINT(1) DEFAULT 0 NOT NULL, CHANGE login_send login_send TINYINT(1) DEFAULT 0 NOT NULL, CHANGE protected protected TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE history DROP FOREIGN KEY FK_27BA704B7597D3FE');
        $this->addSql('DROP INDEX IDX_27BA704B7597D3FE ON history');
        $this->addSql('ALTER TABLE history CHANGE member_id user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE history ADD CONSTRAINT FK_27BA704BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_27BA704BA76ED395 ON history (user_id)');
        $this->addSql('ALTER TABLE respondent DROP FOREIGN KEY FK_409B51507597D3FE');
        $this->addSql('DROP INDEX IDX_409B51507597D3FE ON respondent');
        $this->addSql('ALTER TABLE respondent CHANGE member_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE respondent ADD CONSTRAINT FK_409B5150A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_409B5150A76ED395 ON respondent (user_id)');
    }
}
