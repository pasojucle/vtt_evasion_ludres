<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260719050838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        foreach($this->getIcons() as $icon) {
            $this->addSql('UPDATE agreement SET authorization_icon=:lucide WHERE authorization_icon=:fontAwesome', $icon);
            $this->addSql('UPDATE agreement SET rejection_icon=:lucide WHERE rejection_icon=:fontAwesome', $icon);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        foreach($this->getIcons() as $icon) {
            $this->addSql('UPDATE agreement SET authorization_icon=:fontAwesome WHERE authorization_icon=:lucide', $icon);
            $this->addSql('UPDATE agreement SET rejection_icon=:fontAwesome WHERE rejection_icon=:lucide', $icon);
        }
    }

    private function getIcons(): array
    {
        return [
            [
                'fontAwesome' => '<i class="fa-solid fa-house"></i>', 
                'lucide' => 'lucide:house'
            ],
            [
                'fontAwesome' => '<i class="fa-solid fa-camera"></i>', 
                'lucide' => 'lucide:camera'
            ],
            [
                'fontAwesome' => '<i class="fa-solid fa-slash fa-camera"></i>', 
                'lucide' => 'lucide:camera-off'
            ],
        ];
    }
}
