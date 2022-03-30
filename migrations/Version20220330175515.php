<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220330175515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("DELETE FROM `parameter` WHERE `name` = 'VOTE_CONTENT'");
        $this->addSql("DELETE FROM `parameter` WHERE `name` = 'VOTE_ISSUES'");
        $this->addSql("DELETE FROM `parameter_group` WHERE `name` = 'VOTE'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
