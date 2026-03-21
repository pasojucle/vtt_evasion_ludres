<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260309173749 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        foreach($this->getMessages() as $message) {
            $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES (:section, :name, :label, :content, :levelType, :protected)', $message);
        }
        $this->addSql('ALTER TABLE bike_ride ADD rules VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        foreach($this->getMessages() as $message) {
            $this->addSql('DELETE FROM `message` WHERE name=:name', $message);
        }
        $this->addSql('ALTER TABLE bike_ride DROP rules');
    }

    private function getMessages(): array
    {
        return [
            [
                'name' => 'GUEST_LINK_AUTHENTIFICATION',
                'section' => 3,
                'label' => 'Envoie par mail du lien d\'authentification pour un invité',
                'content' => '<p>Bonjour</p><p>Vous y êtes presque. Pour finaliser votre inscription et rejoindre le départ de la randonnée, cliquez simplement sur le bouton ci-dessous :<p>{{ lien_inscription }}<p><i>Ce lien est valable pendant 60 minutes. Si vous n\'êtes pas à l\'origine de cette demande, vous pouvez ignorer cet email.</i></p>',
                'levelType' => null,
                'protected' => 1,
            ],[
                'name' => 'GUEST_BIKE_RIDE_REGISTRATION',
                'section' => 14,
                'label' => 'Envoie par mail de la confiration d\'inscription à unne rando pour un invité',
                'content' => '<p>Votre inscription à la randonnée "{{ bike_ride_title }}" a bien été prise en compte.</p><p>Nous vous rappelons que pour participer à une sortie, vous devez suivre le réglement de la randonnée</p><p>Bon ride !</p>',
                'levelType' => null,
                'protected' => 1,
            ],
        ];
    }
}
