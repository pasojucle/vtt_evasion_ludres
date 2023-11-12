<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231111150335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE `parameter_group` SET `name`=\'USER\',`label`=\'Adhérent\', `role`=\'NONE\' WHERE `name`=\'CERTIFICATES\'');
        $this->addSql('UPDATE `parameter_group` SET `role`=\'NONE\' WHERE `name`=\'SECOND_HAND\'');

        $parameterGroup = [
            'id' => 8,
            'name' => 'BIKE_RIDE',
            'label' => 'Rando',
            'role' => 'NONE',
        ];

        $this->addSql('INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (:id, :name, :label, :role)', $parameterGroup);

        $parameters = [
            [
                'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT',
                'groupId' => 8
            ],
            [
                'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR',
                'groupId' => 8
            ],
            [
                'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER',
                'groupId' => 8
            ],
        ];
        foreach($parameters as $parameter) {
            $this->addSql('UPDATE `parameter` SET `parameter_group_id`= :groupId WHERE `name`=:name', $parameter);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
