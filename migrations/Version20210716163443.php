<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210716163443 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE level (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, content LONGTEXT NOT NULL, color VARCHAR(7) DEFAULT NULL, monogram VARCHAR(3) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE link (id INT AUTO_INCREMENT NOT NULL, url VARCHAR(255) NOT NULL, title VARCHAR(100) DEFAULT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, is_display_home TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cluster ADD level_id INT DEFAULT NULL, ADD max_users INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cluster ADD CONSTRAINT FK_E5C569945FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_E5C569945FB14BA7 ON cluster (level_id)');
        $this->addSql('ALTER TABLE event ADD type INT NOT NULL, DROP users_per_cluster');
        $this->addSql('ALTER TABLE session ADD is_present TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user ADD level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6495FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6495FB14BA7 ON user (level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster DROP FOREIGN KEY FK_E5C569945FB14BA7');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495FB14BA7');
        $this->addSql('DROP TABLE level');
        $this->addSql('DROP TABLE link');
        $this->addSql('DROP INDEX IDX_E5C569945FB14BA7 ON cluster');
        $this->addSql('ALTER TABLE cluster DROP level_id, DROP max_users');
        $this->addSql('ALTER TABLE event ADD users_per_cluster INT DEFAULT NULL, DROP type');
        $this->addSql('ALTER TABLE session DROP is_present');
        $this->addSql('DROP INDEX IDX_8D93D6495FB14BA7 ON user');
        $this->addSql('ALTER TABLE user DROP level_id');
    }
}
