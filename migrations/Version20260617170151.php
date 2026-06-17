<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260617170151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill_category ADD icon VARCHAR(30) NOT NULL');
    }

    public function postUp(Schema $schema): void
    {
        $categories = [
            ['name' => 'Base', 'icon' => 'lucide:layers'],
            ['name' => 'Environnement', 'icon' => 'lucide:trees'],
            ['name' => 'Pilotage', 'icon' => 'lucide:gauge'],
            ['name' => 'Mécanique', 'icon' => 'lucide:wrench'],
            ['name' => 'Sécurité', 'icon' => 'lucide:shield'],
            ['name' => 'Orientation', 'icon' => 'lucide:compass'],
        ];
        foreach ($categories as $category) {
            $this->connection->executeQuery('UPDATE skill_category SET icon=:icon WHERE name=:name', $category);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE skill_category DROP icon');
    }
}
