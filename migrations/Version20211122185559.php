<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211122185559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`) VALUES
        ('ERROR_USER_AGENT_IGNORE', 'Liste des user-agents à exclure du log des erreurs', 4, '[\"Googlebot\",\"AdsBot-Google\",\"Googlebot-Image\",\"bingbot\",\"bot\",\"ltx71\",\"GoogleImageProxy\",\"SiteLockSpider\"]'),
        ('ERROR_URL_IGNORE', 'Liste des url à exclure du log des erreurs', 4, '[\"wlwmanifest\",\"xmlrpc.php\",\"wp-content\",\"wp-admin\",\".env\",\"yahoo\"]');");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
