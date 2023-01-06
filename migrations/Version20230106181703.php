<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230106181703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE health CHANGE at_least_one_positve_response at_least_one_positve_response TINYINT(1) DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE level CHANGE order_by order_by INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE health CHANGE at_least_one_positve_response at_least_one_positve_response TINYINT(1) DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE level CHANGE order_by order_by INT NOT NULL');
    }
}
