<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231101150612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sworn_certification (id INT AUTO_INCREMENT NOT NULL, licence_id INT DEFAULT NULL, label LONGTEXT NOT NULL, value TINYINT(1) NOT NULL, INDEX IDX_79ED8B7726EF07C9 (licence_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sworn_certification ADD CONSTRAINT FK_79ED8B7726EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE health DROP at_least_one_positve_response');
        $this->addSql('ALTER TABLE registration_step ADD personal TINYINT(1) DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sworn_certification DROP FOREIGN KEY FK_79ED8B7726EF07C9');
        $this->addSql('DROP TABLE sworn_certification');
        $this->addSql('ALTER TABLE registration_step DROP personal');
        $this->addSql('ALTER TABLE health ADD at_least_one_positve_response TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
