<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210528171519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence DROP INDEX UNIQ_1DAAE648A76ED395, ADD INDEX IDX_1DAAE648A76ED395 (user_id)');
        $this->addSql('ALTER TABLE licence DROP number');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence DROP INDEX IDX_1DAAE648A76ED395, ADD UNIQUE INDEX UNIQ_1DAAE648A76ED395 (user_id)');
        $this->addSql('ALTER TABLE licence ADD number VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
