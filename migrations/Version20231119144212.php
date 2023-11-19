<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119144212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('UPDATE `parameter_group` SET `role`=\'NONE\' WHERE `name`=\'REGISTRATION\'');
        $this->addSql('UPDATE `parameter_group` SET `name`=\'TOOLS\',`role`=\'NONE\' WHERE `name`=\'LOG_Error\'');

        $parameterGroups = [
            [
                'id' => 9,
                'name' => 'ORDER',
                'label' => 'Commande',
                'role' => 'NONE',
            ],
            [
                'id' => 10,
                'name' => 'MODAL',
                'label' => 'Pop\'up',
                'role' => 'NONE',
            ],
        ];
        foreach($parameterGroups as $parameterGroup) {
            $this->addSql('INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (:id, :name, :label, :role)', $parameterGroup);
        }

        $parameters = [
            3 => ['EMAIL_ACCOUNT_CREATED', 'SCHOOL_TESTING_REGISTRATION', 'SCHOOL_TESTING_REGISTRATION_MESSAGE', 'REQUIREMENT_SEASON_LICENCE_AT', 'REQUIREMENT_SEASON_LICENCE_MESSAGE', 'EMAIL_END_TESTING', 'EMAIL_REGISTRATION', 'EMAIL_LICENCE_VALIDATE'],
            9 => ['ORDER_ACKNOWLEDGEMENT_MESSAGE'],
            10 => ['MODAL_WINDOW_REGISTRATION_IN_PROGRESS', 'MODAL_WINDOW_ORDER_IN_PROGRESS'],
            5 => ['EMAIL_REGISTRATION_ERROR', 'ERROR_USER_AGENT_IGNORE', 'ERROR_URL_IGNORE'],
        ];

        foreach($parameters as $groupId => $names) {
            foreach($names as $name) {
                $parameter = ['name' => $name, 'groupId' => $groupId];
                $this->addSql('UPDATE `parameter` SET `parameter_group_id`= :groupId WHERE `name`=:name', $parameter);
            }
        }
        $this->addSql('DELETE FROM `parameter` WHERE `name` IN (\'HIKE_MEDICAL_CERTIFICATE_DURATION\', \'SPORT_MEDICAL_CERTIFICATE_DURATION\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
