<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019100930 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $messages = $this->getMessages();
        foreach($messages as $message) {
            $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ((SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionId), :name, :label, :content, :levelType, :protected)', $message);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $messages = $this->getMessages();
        foreach($messages as $message) {
            $this->addSql('DELETE FROM `message` WHERE name LIKE :name', $message);
        }
    }

    private function getMessages(): array
    {    
        return [
            [                
                'name' => 'BIKE_RIDE_MUST_PROVIDE_REGISTRATION',
                'label' => 'Message afficher à un encadrant pour un dossier d\'inscription',
                'content' => '<p>{{ prenom_nom }} a confirmé son inscription pour la saison 2025-2026. Pensez à lui demander les papiers signés.</p>',
                'levelType' => null,
                'protected' => 1,
                'sectionId' => 'BIKE_RIDE',
            ],
            [                
                'name' => 'BIKE_RIDE_END_TESTING',
                'label' => 'Message afficher à un encadrant pour une période de test terminée',
                'content' => '<p>La période d\'essai de {{ prenom_nom }} est terminée. Pensez à lui rappeler de finaliser son inscription s\'il souhaite continuer à participer aux événements du club.</p>',
                'levelType' => null,
                'protected' => 1,
                'sectionId' => 'BIKE_RIDE',
            ],
        ];
    }
}
