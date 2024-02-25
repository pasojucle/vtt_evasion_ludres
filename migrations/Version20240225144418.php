<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240225144418 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE slideshow_directory (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE slideshow_image (id INT AUTO_INCREMENT NOT NULL, directory_id INT DEFAULT NULL, filename VARCHAR(255) NOT NULL, INDEX IDX_C16D18782C94069F (directory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE slideshow_image ADD CONSTRAINT FK_C16D18782C94069F FOREIGN KEY (directory_id) REFERENCES slideshow_directory (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE slideshow_image DROP FOREIGN KEY FK_C16D18782C94069F');
        $this->addSql('DROP TABLE slideshow_directory');
        $this->addSql('DROP TABLE slideshow_image');
    }
}
