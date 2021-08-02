<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210801190147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE content (id INT AUTO_INCREMENT NOT NULL, route VARCHAR(100) NOT NULL, content LONGTEXT NOT NULL, start_at DATETIME DEFAULT NULL, end_at DATETIME DEFAULT NULL, order_by INT NOT NULL, is_active TINYINT(1) NOT NULL, is_flash TINYINT(1) NOT NULL, title VARCHAR(100) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO `content`(`route`, `content`, `order_by`, `is_active`, `is_flash`) VALUES (\'registration_detail\', \'\', 0, 0, 0)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE content');
    }
}
