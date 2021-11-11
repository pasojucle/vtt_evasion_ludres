<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211111192521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity ADD type INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE log_error CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('UPDATE `identity` SET `type` = 2 WHERE `kinship` IS NOT NULL AND `birth_date` IS NOT NULL AND `email` IS NOT NULL');
        $this->addSql('UPDATE `identity` SET `type` = 3 WHERE `kinship` IS NOT NULL AND `birth_date` IS NULL AND `email` IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity DROP type');
        $this->addSql('ALTER TABLE log_error CHANGE created_at created_at DATETIME DEFAULT NULL');
    }
}
