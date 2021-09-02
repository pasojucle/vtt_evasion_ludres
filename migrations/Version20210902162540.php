<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210902162540 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level ADD is_protected TINYINT(1) NOT NULL');
        $maxLevelId = $this->connection->fetchOne('SELECT MAX(id) FROM `level`');
        ++$maxLevelId;
        $level = ['id' => $maxLevelId];
        $this->addSql('INSERT INTO `level`(id, `title`, `content`, `order_by`, `type`, `is_protected`) VALUES (:id, "En cours d\'évaluation","",0,1,1)', $level);
        $this->addSql('UPDATE `user` AS u INNER JOIN licence AS l ON l.user_id = u.id SET u.level_id= :id WHERE l.final = 0', $level);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level DROP is_protected');
    }
}
