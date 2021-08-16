<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210816170524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_step ADD testing_render INT NOT NULL');
        $this->addSql('UPDATE `registration_step` SET `testing_render` = `testing` WHERE 1');
        $this->addSql('ALTER TABLE registration_step DROP testing');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_step DROP testing_render');
        $this->addSql('ALTER TABLE registration_step ADD testing TINYINT(1) NOT NULL');
    }
}
