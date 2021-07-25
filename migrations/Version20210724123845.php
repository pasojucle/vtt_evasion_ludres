<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210724123845 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level ADD type INT NOT NULL, DROP monogram');
        $this->addSql('ALTER TABLE licence ADD final TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('UPDATE `licence` SET `final`= 0 WHERE `testing` = 1');
        $this->addSql('ALTER TABLE licence DROP testing');
        $this->addSql('UPDATE `user` SET `roles`=\'["ROLE_FRAME"]\' WHERE `roles` LIKE \'["ROLE_ACCOMPANIST"]\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level ADD monogram VARCHAR(3) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP type');
        $this->addSql('ALTER TABLE licence ADD testing TINYINT(1) NOT NULL');
        $this->addSql('UPDATE `licence` SET `testing`= 1 WHERE `final` = 0');
        $this->addSql('ALTER TABLE licence DROP final');
    }
}
