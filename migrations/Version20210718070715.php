<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210718070715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD closing_duration INT DEFAULT 1 NOT NULL, DROP closing_at');
        $this->addSql('ALTER TABLE health ADD medical_certificate_date DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE level ADD order_by INT NOT NULL');
        $this->addSql('ALTER TABLE licence ADD created_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD closing_at DATETIME NOT NULL, DROP closing_duration');
        $this->addSql('ALTER TABLE health DROP medical_certificate_date');
        $this->addSql('ALTER TABLE level DROP order_by');
        $this->addSql('ALTER TABLE licence DROP created_at');
    }
}
