<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250215071329 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->connection->executeQuery('UPDATE `content` set `order_by` = `order_by` + 1 WHERE parent_id IS NULL');
        $this->connection->executeQuery('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`, `order_by`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly, :orderBy)', $this->getContent());
    }

    public function postUp(Schema $schema): void
    {
        $stmt = $this->connection->executeQuery('SELECT id FROM content WHERE route = \'splash\'');
        $splash = $stmt->fetchOne();
        $this->connection->executeQuery('INSERT INTO content_background (content_id, background_id) SELECT :contentId, background_id FROM content_background INNER JOIN content ON content_background.content_id = content.id WHERE content.route = \'home\'', ['contentId' => $splash]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM content Where route = :route', $this->getContent());
        $this->addSql('UPDATE `content` set `order_by` = `order_by` - 1 WHERE parent_id IS NULL');
        $stmt = $this->connection->executeQuery('SELECT id FROM content WHERE route = \'splash\'');
        $splash = $stmt->fetchOne();
        $this->addSql('DELETE FROM `content_background` WHERE content_id = :contentId', ['contentId' => $splash]);
    }

    private function getContent(): array
    {
        return [
            'route' => 'splash', 
            'content' => '',
            'isActive' => 1,
            'isFlash' => 0,
            'backgroundOnly' => 1,
            'orderBy' => 0,
        ];
    }
}
