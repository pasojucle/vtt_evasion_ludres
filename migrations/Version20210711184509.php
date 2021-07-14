<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210711184509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster ADD level_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE cluster ADD CONSTRAINT FK_E5C569945FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');
        $this->addSql('CREATE INDEX IDX_E5C569945FB14BA7 ON cluster (level_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cluster DROP FOREIGN KEY FK_E5C569945FB14BA7');
        $this->addSql('DROP INDEX IDX_E5C569945FB14BA7 ON cluster');
        $this->addSql('ALTER TABLE cluster DROP level_id');
    }
}
