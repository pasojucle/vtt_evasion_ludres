<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220325190609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, content LONGTEXT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, disabled TINYINT(1) NOT NULL, is_anonymous TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_issue (id INT AUTO_INCREMENT NOT NULL, survey_id INT DEFAULT NULL, content VARCHAR(255) NOT NULL, response_type INT NOT NULL, INDEX IDX_1EDDD106B3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_response (id INT AUTO_INCREMENT NOT NULL, survey_issue_id INT NOT NULL, user_id INT DEFAULT NULL, value LONGTEXT DEFAULT NULL, uuid VARCHAR(23) NOT NULL, INDEX IDX_628C4DDC28DE3AB (survey_issue_id), INDEX IDX_628C4DDCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_user (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_4B7AD682B3FE509D (survey_id), INDEX IDX_4B7AD682A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE survey_issue ADD CONSTRAINT FK_1EDDD106B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_response ADD CONSTRAINT FK_628C4DDC28DE3AB FOREIGN KEY (survey_issue_id) REFERENCES survey_issue (id)');
        $this->addSql('ALTER TABLE survey_response ADD CONSTRAINT FK_628C4DDCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        
        $this->addSql('INSERT INTO survey SELECT * FROM vote');
        $this->addSql('INSERT INTO survey_issue (id, survey_id, content, response_type) SELECT id, vote_id, content, response_type FROM vote_issue');
        $this->addSql('INSERT INTO survey_response (id, survey_issue_id, user_id, value, uuid) SELECT id, vote_issue_id, user_id, value, uuid FROM vote_response');
        $this->addSql('INSERT INTO survey_user (id, survey_id, user_id, created_at) SELECT id, vote_id, user_id, created_at FROM vote_user');


        // $this->addSql('ALTER TABLE vote_issue DROP FOREIGN KEY FK_43C441F572DCDAFC');
        // $this->addSql('ALTER TABLE vote_user DROP FOREIGN KEY FK_3AF1277872DCDAFC');
        // $this->addSql('ALTER TABLE vote_response DROP FOREIGN KEY FK_C024A2D2A0250CA5');
        // $this->addSql('DROP TABLE vote');
        // $this->addSql('DROP TABLE vote_issue');
        // $this->addSql('DROP TABLE vote_response');
        // $this->addSql('DROP TABLE vote_user');
   }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey_issue DROP FOREIGN KEY FK_1EDDD106B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD682B3FE509D');
        $this->addSql('ALTER TABLE survey_response DROP FOREIGN KEY FK_628C4DDC28DE3AB');
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, disabled TINYINT(1) NOT NULL, is_anonymous TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vote_issue (id INT AUTO_INCREMENT NOT NULL, vote_id INT DEFAULT NULL, content VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, response_type INT NOT NULL, INDEX IDX_43C441F572DCDAFC (vote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vote_response (id INT AUTO_INCREMENT NOT NULL, vote_issue_id INT NOT NULL, user_id INT DEFAULT NULL, value LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, uuid VARCHAR(23) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_C024A2D2A76ED395 (user_id), INDEX IDX_C024A2D2A0250CA5 (vote_issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE vote_user (id INT AUTO_INCREMENT NOT NULL, vote_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3AF12778A76ED395 (user_id), INDEX IDX_3AF1277872DCDAFC (vote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE vote_issue ADD CONSTRAINT FK_43C441F572DCDAFC FOREIGN KEY (vote_id) REFERENCES vote (id)');
        $this->addSql('ALTER TABLE vote_response ADD CONSTRAINT FK_C024A2D2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote_response ADD CONSTRAINT FK_C024A2D2A0250CA5 FOREIGN KEY (vote_issue_id) REFERENCES vote_issue (id)');
        $this->addSql('ALTER TABLE vote_user ADD CONSTRAINT FK_3AF12778A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote_user ADD CONSTRAINT FK_3AF1277872DCDAFC FOREIGN KEY (vote_id) REFERENCES vote (id)');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP TABLE survey_issue');
        $this->addSql('DROP TABLE survey_response');
        $this->addSql('DROP TABLE survey_user');
    }
}
