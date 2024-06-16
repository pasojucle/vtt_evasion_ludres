<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240612163523 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', min_age INT DEFAULT NULL, max_age INT DEFAULT NULL, is_disabled TINYINT(1) DEFAULT 0 NOT NULL, public TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO `notification`(`id`, `title`, `content`, `start_at`, `end_at`, `max_age`, `min_age`, `is_disabled`) SELECT `id`, `title`, `content`, `start_at`, `end_at`, `max_age`, `min_age`, `is_disabled` FROM `notification`');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE second_hand ADD valided_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE `second_hand` SET `valided_at`=`created_at` WHERE `valid` = 1');
        $this->addSql('ALTER TABLE second_hand DROP valid');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, start_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', end_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', min_age INT DEFAULT NULL, max_age INT DEFAULT NULL, is_disabled TINYINT(1) DEFAULT 0 NOT NULL, public TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('INSERT INTO `notification`(`id`, `title`, `content`, `start_at`, `end_at`, `max_age`, `min_age`, `is_disabled`) SELECT `id`, `title`, `content`, `start_at`, `end_at`, `max_age`, `min_age`, `is_disabled` FROM `notification`');
        $this->addSql('DROP TABLE notification');
        $this->addSql('ALTER TABLE second_hand ADD valid TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('UPDATE `second_hand` SET `valid`= 1 WHERE `valided_at IS NOT NULL');
        $this->addSql('ALTER TABLE second_hand DROP valided_at');

    }
}
