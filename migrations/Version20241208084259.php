<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241208084259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
  
        foreach ($this->getMessages() as $message) {
            $this->addSql('INSERT INTO `message`(`name`, `label`, `content`, `section_id`) VALUES (:name, :label, :content, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $message);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        foreach ($this->getMessages() as $message) {
            $this->addSql('DELETE FROM `message` WHERE `name` = :name', $message);
        }
    }

    private function getMessages(): array
    {
        return [
            [
                'name' => 'CONFIRM_FRAMER_PARTICIPATION_EMAIL',
                'label' => 'Message de confirmation de particpation d\'un encadrant à une rando de l\'école vtt',
                'content' => '<p>Vous êtes en disponibilité si besoins  à la sortie {{ rando }}.</p><p>Merci de confirmer votre participation ou votre absence en suivant <a href="{{ lien_modifier_disponibilite }}">ce lien</a>.</p>',
                'parameterGroupName' => 'BIKE_RIDE'
            ],
        ];
    }
}
