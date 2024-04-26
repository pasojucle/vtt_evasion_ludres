<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240426111522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $message = [
            'name' => 'SECOND_HAND_CONTACT_CONFIRM',
            'label' => 'Message de confirmation pour contacter un vendeur',
            'content' => '<p>Un message automatique va être envoyé au vendeur afin de vous recontacter, voulez-vous envoyer ce message automatique ?</p>',
            'levelType' => null,
            'protected' => 1,
            'sectionName' => 'SECOND_HAND',
        ];
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES ( (SELECT `id` FROM `parameter_group` WHERE name LIKE :sectionName), :name, :label, :content, :levelType, :protected)', $message);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `message` WHERE `name`=\'SECOND_HAND_CONTACT_CONFIRM\'');
    }
}
