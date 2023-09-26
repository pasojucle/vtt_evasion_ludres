<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230924153441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand ADD disabled TINYINT(1) DEFAULT 0 NOT NULL');

        $parameterGroup = [
            'id' => 7,
            'name' => 'SECOND_HAND',
            'label' => 'Occasion',
            'role' => 'ROLE_ADMIN',
       ];

       $this->addSql('INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (:id, :name, :label, :role)', $parameterGroup);

        $parameters = [
            [
                'name' => 'SECOND_HAND_DURATION',
                'label' => 'Durré d\'affichage d\'une annonce d\'occasion (en jours)',
                'type' => 2,
                'value' => 30,
                'group' => 7,
            ],
            [
                'name' => 'SECOND_HAND_DISABLED_MESSAGE',
                'label' => 'Message du mail à la désactivation d\'une annonce',
                'type' => 1,
                'value' => '<p>Votre annonce "{{ nom_annonce }}" a été crée il y a plus de {{ durree }} jours.</p><p> Elle a donc été désactivée.</p><p>Si toute fois, vous souhaitez la remettre en ligne suivez le lien pour la réactiver <a href="{{ url }}">{{ url }}</a></p>',
                'group' => 7,
            ],
        ];
        foreach($parameters as $parameter) {
            $this->addSql('INSERT INTO `parameter` (`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, :group)', $parameter);
        }
        $this->addSql('UPDATE `parameter` SET `parameter_group_id`= 7 WHERE `name` LIKE \'SECOND_HAND_CONTACT\'');    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE second_hand DROP disabled');
    }
}
