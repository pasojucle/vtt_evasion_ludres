<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220707180929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content ADD parent_id INT DEFAULT NULL, CHANGE content content LONGTEXT DEFAULT NULL, CHANGE order_by order_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9727ACA70 FOREIGN KEY (parent_id) REFERENCES content (id)');
        $this->addSql('CREATE INDEX IDX_FEC530A9727ACA70 ON content (parent_id)');

        $id = $this->connection->fetchOne('SELECT MAX(id) FROM content');
        ++$id;
        $this->addSql('INSERT INTO `content`(`id`, `route`, `content`, `order_by`, `is_active`, `is_flash`) VALUES (:id, :route, :content, :orderBy, :isActive, :isFlash)', ['id' => $id, 'route' => 'home', 'content' => '', 'orderBy' => 0, 'isActive' => 1, 'isFlash' => 0]);
        $this->addSql('UPDATE `content` SET `parent_id` = :id WHERE `route` = \'home\' AND `id`!= :id', ['id' => $id] );

        $this->addSql('INSERT INTO `content`(`route`, `content`, `order_by`, `is_active`, `is_flash`) VALUES (:route, :content, :orderBy, :isActive, :isFlash)', ['route' => 'registration_membership_fee', 'content' => '', 'orderBy' => 5, 'isActive' => 1, 'isFlash' => 0]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9727ACA70');
        $this->addSql('DROP INDEX IDX_FEC530A9727ACA70 ON content');
        $this->addSql('ALTER TABLE content DROP parent_id, CHANGE content content LONGTEXT NOT NULL, CHANGE order_by order_by INT NOT NULL');
    }
}
