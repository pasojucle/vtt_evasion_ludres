<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261107161810 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("UPDATE message SET level_type = 'none' WHERE level_type IS NULL");
        $this->addSql('ALTER TABLE message CHANGE level_type level_type VARCHAR(255) DEFAULT \'none\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE message CHANGE level_type level_type VARCHAR(255) DEFAULT NULL');
        $this->addSql("UPDATE message SET level_type = null WHERE level_type = 'none'");
    }
}
