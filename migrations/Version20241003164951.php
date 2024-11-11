<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241003164951 extends AbstractMigration
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
                'name' => 'NEW_SESSION_MEMBER',
                'label' => 'Message de confirmation à l\'inscription d\'un adhérent à une rando',
                'content' => '<p>Votre inscription à la sortie {{ rando }} a bien été prise en compte.</p><p> Si vous ne pouvez plus participez pas à cette sortie, il est impératif de se désinsrire, aller dans Mon programme perso et faire "Se désinscrire"</p>',
                'parameterGroupName' => 'BIKE_RIDE'
            ],
            [
                'name' => 'NEW_SESSION_FRAMER',
                'label' => 'Message de confirmation à l\'inscription d\'un encadrant à une rando',
                'content' => '<p>Votre disponibilité à la sortie {{ rando }} a bien été prise en compte.</p><p> En cas de changement, il est impératif de se modifier sa disponibilité, aller dans dans Mon programme perso et faire "Modifier"</p>',
                'parameterGroupName' => 'BIKE_RIDE'
            ],
        ];
    }
}
