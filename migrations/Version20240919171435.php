<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919171435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE skill (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, level_id INT NOT NULL, content LONGTEXT NOT NULL, INDEX IDX_5E3DE47712469DE2 (category_id), INDEX IDX_5E3DE4775FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE skill_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE47712469DE2 FOREIGN KEY (category_id) REFERENCES skill_category (id)');
        $this->addSql('ALTER TABLE skill ADD CONSTRAINT FK_5E3DE4775FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');

        $this->addSql('CREATE TABLE cluster_skill (cluster_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_286A45FDC36A3328 (cluster_id), INDEX IDX_286A45FD5585C142 (skill_id), PRIMARY KEY(cluster_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_skill (user_id INT NOT NULL, skill_id INT NOT NULL, INDEX IDX_BCFF1F2FA76ED395 (user_id), INDEX IDX_BCFF1F2F5585C142 (skill_id), PRIMARY KEY(user_id, skill_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cluster_skill ADD CONSTRAINT FK_286A45FDC36A3328 FOREIGN KEY (cluster_id) REFERENCES cluster (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cluster_skill ADD CONSTRAINT FK_286A45FD5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_skill ADD CONSTRAINT FK_BCFF1F2F5585C142 FOREIGN KEY (skill_id) REFERENCES skill (id) ON DELETE CASCADE');
    
      }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE47712469DE2');
        $this->addSql('ALTER TABLE skill DROP FOREIGN KEY FK_5E3DE4775FB14BA7');
        $this->addSql('DROP TABLE skill');
        $this->addSql('DROP TABLE skill_category');

        $this->addSql('ALTER TABLE cluster_skill DROP FOREIGN KEY FK_286A45FDC36A3328');
        $this->addSql('ALTER TABLE cluster_skill DROP FOREIGN KEY FK_286A45FD5585C142');
        $this->addSql('ALTER TABLE user_skill DROP FOREIGN KEY FK_BCFF1F2FA76ED395');
        $this->addSql('ALTER TABLE user_skill DROP FOREIGN KEY FK_BCFF1F2F5585C142');
        $this->addSql('DROP TABLE cluster_skill');
        $this->addSql('DROP TABLE user_skill');
    }
}
