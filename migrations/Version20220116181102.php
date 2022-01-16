<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220116181102 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vote (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(30) NOT NULL, content LONGTEXT NOT NULL, start_at DATETIME NOT NULL, end_at DATETIME NOT NULL, disabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_issue (id INT AUTO_INCREMENT NOT NULL, vote_id INT NOT NULL, content VARCHAR(255) NOT NULL, response_type INT NOT NULL, INDEX IDX_43C441F572DCDAFC (vote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_response (id INT AUTO_INCREMENT NOT NULL, vote_issue_id INT NOT NULL, value LONGTEXT DEFAULT NULL, uuid VARCHAR(23) NOT NULL, INDEX IDX_C024A2D2A0250CA5 (vote_issue_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_user (id INT AUTO_INCREMENT NOT NULL, vote_id INT NOT NULL, user_id INT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_3AF1277872DCDAFC (vote_id), INDEX IDX_3AF12778A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vote_issue ADD CONSTRAINT FK_43C441F572DCDAFC FOREIGN KEY (vote_id) REFERENCES vote (id)');
        $this->addSql('ALTER TABLE vote_response ADD CONSTRAINT FK_C024A2D2A0250CA5 FOREIGN KEY (vote_issue_id) REFERENCES vote_issue (id)');
        $this->addSql('ALTER TABLE vote_user ADD CONSTRAINT FK_3AF1277872DCDAFC FOREIGN KEY (vote_id) REFERENCES vote (id)');
        $this->addSql('ALTER TABLE vote_user ADD CONSTRAINT FK_3AF12778A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        
        $this->addSql("INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (7, 'VOTE', 'Vote', 'ROLE_ADMIN')");
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES
        ('VOTE_CONTENT', 'Contenu d\'un vote pour une assemblée générale', 1, '<p>L\'assemblée générale du club aura lieu</p>', 7),
        ('VOTE_ISSUES', 'Questions par défault d\'un vote', 4, '{\"1\":\"Approuvez-vous le rapport moral ?\",\"2_\":\"Approuvez-vous le bilan financier ?\",\"3\":\"Avez-vous des questions, avis, conseils, suggestions à formuler ? Si oui, veuillez les formuler ci-dessous :\"}', 7)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote_issue DROP FOREIGN KEY FK_43C441F572DCDAFC');
        $this->addSql('ALTER TABLE vote_user DROP FOREIGN KEY FK_3AF1277872DCDAFC');
        $this->addSql('ALTER TABLE vote_response DROP FOREIGN KEY FK_C024A2D2A0250CA5');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE vote_issue');
        $this->addSql('DROP TABLE vote_response');
        $this->addSql('DROP TABLE vote_user');
    }
}
