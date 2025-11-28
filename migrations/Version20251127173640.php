<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127173640 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD family_member_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE licence ADD CONSTRAINT FK_1DAAE648BC594993 FOREIGN KEY (family_member_id) REFERENCES licence (id)');
        $this->addSql('CREATE INDEX IDX_1DAAE648BC594993 ON licence (family_member_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence DROP FOREIGN KEY FK_1DAAE648BC594993');
        $this->addSql('DROP INDEX IDX_1DAAE648BC594993 ON licence');
        $this->addSql('ALTER TABLE licence DROP family_member_id');
    }
}
