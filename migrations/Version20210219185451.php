<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210219185451 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE school_list (id INT AUTO_INCREMENT NOT NULL, virtual_directory_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, INDEX IDX_5E027F5AB928042 (virtual_directory_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE virtualdirectory (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, title VARCHAR(50) NOT NULL, INDEX IDX_6355A948727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE school_list ADD CONSTRAINT FK_5E027F5AB928042 FOREIGN KEY (virtual_directory_id) REFERENCES virtualdirectory (id)');
        $this->addSql('ALTER TABLE virtualdirectory ADD CONSTRAINT FK_6355A948727ACA70 FOREIGN KEY (parent_id) REFERENCES virtualdirectory (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE school_list DROP FOREIGN KEY FK_5E027F5AB928042');
        $this->addSql('ALTER TABLE virtualdirectory DROP FOREIGN KEY FK_6355A948727ACA70');
        $this->addSql('DROP TABLE school_list');
        $this->addSql('DROP TABLE virtualdirectory');
    }
}
