<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410165459 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ((SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionId), :name, :label, :content, :levelType, :protected)', $this->getMessage());
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `message` WHERE name LIKE :name', $this->getMessage());
    }

    private function getMessage(): array
    {    
        return [              
            'name' => 'CLUSTER_EXPORT_MESSAGE',
            'label' => 'Message à afficher pour un export de groupe',
            'content' => '<p>Votre groupe {{ groupe }} a été validé.</p><p>Vous pouvez dès à présent télécharger les informations des participants de votre groupe.</p>',
            'levelType' => null,
            'protected' => 1,
            'sectionId' => 'BIKE_RIDE',
        ];
    }
}
