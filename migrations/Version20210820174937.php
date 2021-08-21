<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210820174937 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD status INT NOT NULL');
        $this->addSql('UPDATE `licence` SET `status`= 3 WHERE `is_download` = 1');
        $this->addSql('UPDATE `licence` SET `status`= 4 WHERE `valid` = 1 and final = 0');
        $this->addSql('UPDATE `licence` SET `status`= 5 WHERE `valid` = 1 and final = 1');
        $this->addSql('ALTER TABLE licence DROP valid, DROP is_download');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD valid TINYINT(1) NOT NULL, ADD is_download TINYINT(1) DEFAULT \'0\' NOT NULL, DROP status');
    }
}
