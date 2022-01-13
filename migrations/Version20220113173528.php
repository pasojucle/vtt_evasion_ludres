<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220113173528 extends AbstractMigration
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
        $this->addSql('CREATE TABLE vote_response (id INT AUTO_INCREMENT NOT NULL, vote_issue_id INT NOT NULL, user_id INT NOT NULL, value INT NOT NULL, INDEX IDX_C024A2D2A0250CA5 (vote_issue_id), INDEX IDX_C024A2D2A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vote_issue ADD CONSTRAINT FK_43C441F572DCDAFC FOREIGN KEY (vote_id) REFERENCES vote (id)');
        $this->addSql('ALTER TABLE vote_response ADD CONSTRAINT FK_C024A2D2A0250CA5 FOREIGN KEY (vote_issue_id) REFERENCES vote_issue (id)');
        $this->addSql('ALTER TABLE vote_response ADD CONSTRAINT FK_C024A2D2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote_issue DROP FOREIGN KEY FK_43C441F572DCDAFC');
        $this->addSql('ALTER TABLE vote_response DROP FOREIGN KEY FK_C024A2D2A0250CA5');
        $this->addSql('DROP TABLE vote');
        $this->addSql('DROP TABLE vote_issue');
        $this->addSql('DROP TABLE vote_response');
    }
}
