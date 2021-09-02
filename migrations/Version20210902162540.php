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
        $this->addSql('ALTER TABLE level ADD is_protected TINYINT(1) NOT NULL, ADD is_deleted TINYINT(1) NOT NULL');
        $maxLevelId = $this->connection->fetchOne('SELECT MAX(id) FROM `level`');
        ++$maxLevelId;
        $level = ['id' => $maxLevelId];
        $this->addSql('INSERT INTO `level`(id, `title`, `content`, `order_by`, `type`, `is_protected`, `is_deleted`) VALUES (:id, "En cours d\'évaluation","",0,1,1,0)', $level);
        $this->addSql('UPDATE `user` AS u INNER JOIN licence AS l ON l.user_id = u.id SET u.level_id= :id WHERE l.final = 0', $level);
        $events = $this->connection->fetchAllAssociative('SELECT `id` FROM `event` WHERE `type`= 2');
        if (!empty($events)) {
            foreach($events as $event) {
                $cluster = [
                    'eventId' => $event['id'],
                    'title' => "En cours d\'évaluation",
                    'levelId' => $maxLevelId,
                ];
                $this->addSql('INSERT INTO `cluster`(`event_id`, `title`, `level_id`) VALUES (:eventId, :title, :levelId)', $cluster);
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE level DROP is_protected, DROP is_deleted');
    }
}
