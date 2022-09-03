<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220903062742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `level`(`id`,`title`, `content`, `order_by`, `type`, `is_protected`, `is_deleted`) VALUES (11, \'Adulte hors encadrement\',\'Adulte hors encadrement\',6,3,1,0)');
        $this->addSql('UPDATE `user` SET `level_id`= 11 WHERE `level_id` IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
