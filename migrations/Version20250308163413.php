<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250308163413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride ADD registration_closed_message LONGTEXT DEFAULT NULL');
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ((SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionId), :name, :label, :content, :levelType, :protected)', $this->getMessage());

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride DROP registration_closed_message');
        $this->addSql('DELETE FROM `message` WHERE name LIKE :name', $this->getMessage());

    }

    private function getMessage(): array
    {    
        return [              
            'name' => 'REGISTRATION_CLOSED_DEFAULT_MESSAGE',
            'label' => 'Message par défaut afficher à l\'inscription d\'une sortie close',
            'content' => '<p>Les inscriptions à cette sortie sont closes.</p>',
            'levelType' => null,
            'protected' => 1,
            'sectionId' => 'BIKE_RIDE',
        ];
    }
}
