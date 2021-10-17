<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211017191132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE `user` SET `roles`='[\"ROLE_REGISTER\", \"ROLE_FRAME\"]' WHERE `licence_number` = '771815'");

    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE `user` SET `roles`='[\"ROLE_REGISTER\"]' WHERE `licence_number` = '771815'");

    }
}
