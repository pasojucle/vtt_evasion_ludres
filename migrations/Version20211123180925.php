<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211123180925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE parameter_group (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, label VARCHAR(255) NOT NULL, role VARCHAR(25) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE parameter ADD parameter_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE parameter ADD CONSTRAINT FK_2A979110132604DB FOREIGN KEY (parameter_group_id) REFERENCES parameter_group (id)');
        $this->addSql('CREATE INDEX IDX_2A979110132604DB ON parameter (parameter_group_id)');
        $parameterGroups = [
            [ 'id' => 1, 'name' => 'BIKE_RIDE', 'label' => 'Sorties', 'role' => 'ROLE_ADMIN',],
            [ 'id' => 2, 'name' => 'EMAIL', 'label' => 'Email', 'role' => 'ROLE_ADMIN',],
            [ 'id' => 3, 'name' => 'REGISTRATION', 'label' => 'Inscription', 'role' => 'ROLE_ADMIN',],
            [ 'id' => 4, 'name' => 'REGISTRATION_CERTIFICATE', 'label' => 'Attestation d\'inscription pour CE', 'role' => 'ROLE_ADMIN',],
            [ 'id' => 5, 'name' => 'LOG_Error', 'label' => 'Journal des erreurs', 'role' => 'ROLE_SUPER_ADMIN',],
            [ 'id' => 6, 'name' => 'MAINTENANCE', 'label' => 'Maintenance', 'role' => 'ROLE_SUPER_ADMIN', ],
            
        ];
        foreach($parameterGroups as $parameterGroup) {
            $this->addSql('INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (:id, :name, :label, :role)', $parameterGroup);
        }
        $parametersByGroup = [
            1 => ['EVENT_SCHOOL_CONTENT', 'EVENT_ADULT_CONTENT', 'EVENT_HOLIDAYS_CONTENT'],
            2 => ['EMAIL_FORM_CONTACT', 'EMAIL_REGISTRATION', 'EMAIL_END_TESTING', 'EMAIL_LICENCE_VALIDATE', 'EMAIL_REGISTRATION_ERROR'],
            3 => ['SCHOOL_TESTING_REGISTRATION'],
            4 => ['REGISTRATION_CERTIFICATE_ADULT', 'REGISTRATION_CERTIFICATE_SCHOOL'],
            5 => ['ERROR_USER_AGENT_IGNORE', 'ERROR_URL_IGNORE'],
            6 => ['MAINTENANCE_MODE'],
        ];

        foreach($parametersByGroup as $parameterGroup => $parameters) {
            foreach($parameters as $parameterName) {
                $parameter = ['parameterGroup' => $parameterGroup, 'parameterName' => $parameterName];
                $this->addSql('UPDATE `parameter` SET `parameter_group_id`=:parameterGroup WHERE `name`=:parameterName', $parameter);
            }
        }

        $this->addSql('ALTER TABLE parameter CHANGE parameter_group_id parameter_group_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parameter DROP FOREIGN KEY FK_2A979110132604DB');
        $this->addSql('DROP TABLE parameter_group');
        $this->addSql('DROP INDEX IDX_2A979110132604DB ON parameter');
        $this->addSql('ALTER TABLE parameter DROP parameter_group_id');
    }
}
