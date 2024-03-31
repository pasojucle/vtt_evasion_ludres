<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240331090316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity DROP birth_department');
        $this->addSql('UPDATE `identity` LEFT JOIN commune ON commune.id=identity.birth_commune_id SET `birth_commune_id`= NULL WHERE commune.department_id IS NULL');
        $this->addSql('DELETE FROM `commune` WHERE `department_id` IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE identity ADD birth_department VARCHAR(100) DEFAULT NULL');
    }
}
