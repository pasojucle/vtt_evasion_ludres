<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220827135933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commune (id VARCHAR(10) NOT NULL, department_id VARCHAR(3) DEFAULT NULL, name VARCHAR(75) NOT NULL, INDEX IDX_E2E2D1EEAE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id VARCHAR(3) NOT NULL, name VARCHAR(75) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EEAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE address ADD commune_id VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F81131A4F72 FOREIGN KEY (commune_id) REFERENCES commune (id)');
        $this->addSql('CREATE INDEX IDX_D4E6F81131A4F72 ON address (commune_id)');
        $this->addSql('ALTER TABLE identity ADD birth_commune_id VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE identity ADD CONSTRAINT FK_6A95E9C422D2C4D7 FOREIGN KEY (birth_commune_id) REFERENCES commune (id)');
        $this->addSql('CREATE INDEX IDX_6A95E9C422D2C4D7 ON identity (birth_commune_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE address DROP FOREIGN KEY FK_D4E6F81131A4F72');
        $this->addSql('ALTER TABLE identity DROP FOREIGN KEY FK_6A95E9C422D2C4D7');
        $this->addSql('ALTER TABLE commune DROP FOREIGN KEY FK_E2E2D1EEAE80F5DF');
        $this->addSql('DROP TABLE commune');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP INDEX IDX_D4E6F81131A4F72 ON address');
        $this->addSql('ALTER TABLE address DROP commune_id');
        $this->addSql('DROP INDEX IDX_6A95E9C422D2C4D7 ON identity');
        $this->addSql('ALTER TABLE identity DROP birth_commune_id');
    }
}
