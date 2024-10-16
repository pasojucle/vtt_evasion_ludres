<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230728170951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_change (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, entity VARCHAR(100) NOT NULL, entity_id INT NOT NULL, value LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', season INT NOT NULL, INDEX IDX_EE093ECAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_change ADD CONSTRAINT FK_EE093ECAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_change DROP FOREIGN KEY FK_EE093ECAA76ED395');
        $this->addSql('DROP TABLE registration_change');
    }
}
