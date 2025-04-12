<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227183400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content ADD youtube_embed LONGTEXT DEFAULT NULL, ADD kind ENUM(\'home-flash\', \'home-content\', \'background-only\', \'background-and-text\', \'carrousel-and-text\', \'video-and-text\') NOT NULL COMMENT \'(DC2Type:ContentKind)\'');
        $this->addSql('UPDATE content set kind = \'home-flash\' WHERE is_flash = 1');
        $this->addSql('UPDATE content set kind = \'home-content\' WHERE is_flash = 0 AND parent_id IS NOT NULL');
        $this->addSql('UPDATE content set kind = \'background-only\' WHERE background_only = 1');
        $this->addSql('UPDATE `content` set kind = \'carrousel-and-text\' WHERE route IN (:routes)', ['routes' => $this->getCarrouselAndTextRoutes()], ['routes' => ArrayParameterType::STRING]);
        $this->addSql('UPDATE content set kind = \'background-and-text\' WHERE route NOT IN (:routes) AND parent_id IS NULL AND background_only = 0', ['routes' => $this->getCarrouselAndTextRoutes()], ['routes' => ArrayParameterType::STRING]);

        $this->addSql('ALTER TABLE content DROP is_flash, DROP background_only');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE content ADD is_flash TINYINT(1) NOT NULL, ADD background_only TINYINT(1) DEFAULT 0 NOT NULL');

        $this->addSql('UPDATE content set is_flash = 1 WHERE kind = \'home-flash\'');
        $this->addSql('UPDATE content set is_flash = 0 WHERE kind = \'home-content\'');
        $this->addSql('ALTER TABLE content DROP kind, DROP youtube_embed');
    }

    private function getCarrouselAndTextRoutes(): array
    {
        $routes = ['club', 'school_overview', 'school_operating', 'school_practices', 'school_equipment', 'school_documentation'];

        return $routes;
    }
}
