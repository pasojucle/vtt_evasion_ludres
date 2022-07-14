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
        $this->addSql('ALTER TABLE content ADD parent_id INT DEFAULT NULL, ADD background_only TINYINT(1) DEFAULT 0 NOT NULL, CHANGE content content LONGTEXT DEFAULT NULL, CHANGE order_by order_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE content ADD CONSTRAINT FK_FEC530A9727ACA70 FOREIGN KEY (parent_id) REFERENCES content (id)');
        $this->addSql('CREATE INDEX IDX_FEC530A9727ACA70 ON content (parent_id)');

        $id = $this->connection->fetchOne('SELECT MAX(id) FROM content');
        ++$id;
        $this->addSql('INSERT INTO `content`(`id`, `route`, `content`, `order_by`, `is_active`, `is_flash`, `background_only`) VALUES (:id, :route, :content, :orderBy, :isActive, :isFlash, :backgroundOnly)', ['id' => $id, 'route' => 'home', 'content' => '', 'orderBy' => 0, 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 1]);
        $this->addSql('UPDATE `content` SET `parent_id` = :id WHERE `route` = \'home\' AND `id`!= :id', ['id' => $id] );

        $contents = [
            ['route' => 'registration_membership_fee', 'content' => '', 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 0],
            ['route' => 'schedule', 'content' => '', 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 1],
            ['route' => 'user_account', 'content' => '', 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 1],
            ['route' => 'links', 'content' => '', 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 1],
            ['route' => 'default', 'content' => '', 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 1],
        ];
        foreach($contents as $content) {
            $this->addSql('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly)', $content);
        }

        $routes = [
            'club', 
            'school_practices',
            'school_overview', 
            'school_operating',
            'school_equipment',
            'schedule',
            'registration_detail',
            'registration_membership_fee',
            'links',
            'contact', 
            'rules', 
            'legal_notices',
            'login_help',
            'default',
            'user_account',
        ];

        foreach($routes as $key => $route) {
            $this->addSql('UPDATE `content` SET `order_by`= :key WHERE `route` = :route', ['key' => $key, 'route' => $route]);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content DROP FOREIGN KEY FK_FEC530A9727ACA70');
        $this->addSql('DROP INDEX IDX_FEC530A9727ACA70 ON content');
        $this->addSql('ALTER TABLE content DROP parent_id, DROP background_only, CHANGE order_by order_by INT NOT NULL');
    }
}
