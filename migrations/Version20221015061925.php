<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221015061925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameterGroup = [
             'id' => 7,
             'name' => 'MODAL_WINDOW',
             'label' => 'pop\'up',
             'role' => 'ROLE_ADMIN',
        ];

        $this->addSql('INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (:id, :name, :label, :role)', $parameterGroup);

        $parameter = [
            'content' => '<p>Votre commande en cours n\'a pas été validée. Souhaitez-vous valider votre commande ?</P>',
            'name' => 'MODAL_WINDOW_ORDER_IN_PROGRESS',
            'label' => 'Message du pop\'up pour la commande en cours',
            'type' => 1,
            'parameterGroup' => 7,
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :content, :parameterGroup)', $parameter);

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
