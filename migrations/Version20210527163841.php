<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210527163841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_step_registration_type DROP FOREIGN KEY FK_C44C8C21853DD935');
        $this->addSql('DROP TABLE registration_step_registration_type');
        $this->addSql('DROP TABLE registration_type');
        $this->addSql('ALTER TABLE licence ADD category INT NOT NULL');
        $this->addSql('ALTER TABLE registration_step ADD category INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_step_registration_type (registration_step_id INT NOT NULL, registration_type_id INT NOT NULL, INDEX IDX_C44C8C2133C34B3A (registration_step_id), INDEX IDX_C44C8C21853DD935 (registration_type_id), PRIMARY KEY(registration_step_id, registration_type_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE registration_type (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE registration_step_registration_type ADD CONSTRAINT FK_C44C8C2133C34B3A FOREIGN KEY (registration_step_id) REFERENCES registration_step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration_step_registration_type ADD CONSTRAINT FK_C44C8C21853DD935 FOREIGN KEY (registration_type_id) REFERENCES registration_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE licence DROP category');
        $this->addSql('ALTER TABLE registration_step DROP category');
    }
}
