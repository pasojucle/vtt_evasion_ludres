<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114112059 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE documentation (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, order_by INT NOT NULL, filename VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $content = [
            'route' => 'school_documentation', 
            'content' => '<h1>Documentations</h1><p>dans le cadre de la formation pour les critérium et autre épreuves.</p>',
            'isActive' => 1,
            'isFlash' => 0,
            'backgroundOnly' => 0,
            'orderBy' => 4,
        ];

        $this->addSql('INSERT INTO `content`(`route`, `content`, `is_active`, `is_flash`, `background_only`, `order_by`) VALUES (:route, :content, :isActive, :isFlash, :backgroundOnly, :orderBy)', $content);
        
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE documentation');
    }
}
