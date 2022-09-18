<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220918054556 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $contents = [
            ['route' => 'registration_tuto', 'content' => '<p><object width="100%" height="700" type="application/pdf" data="/images/tuto_inscription.pdf"><p>Le fichier PDF ne peut pas être affiché avec ce navigateur.</p></object></p>', 'isActive' => 1, 'isFlash' => 0, 'backgroundOnly' => 0],
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
            'registration_tuto',
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

    }
}
