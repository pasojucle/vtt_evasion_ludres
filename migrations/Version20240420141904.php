<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Level;
use App\Entity\Parameter;
use App\Entity\BikeRideType;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240420141904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_ride_type_message (bike_ride_type_id INT NOT NULL, message_id INT NOT NULL, INDEX IDX_68B5B8DEC9743080 (bike_ride_type_id), INDEX IDX_68B5B8DE537A1329 (message_id), PRIMARY KEY(bike_ride_type_id, message_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE message (id INT AUTO_INCREMENT NOT NULL, section_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, label VARCHAR(150) NOT NULL, content LONGTEXT NOT NULL, level_type INT DEFAULT NULL, protected TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_B6BD307FD823E37A (section_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bike_ride_type_message ADD CONSTRAINT FK_68B5B8DEC9743080 FOREIGN KEY (bike_ride_type_id) REFERENCES bike_ride_type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE bike_ride_type_message ADD CONSTRAINT FK_68B5B8DE537A1329 FOREIGN KEY (message_id) REFERENCES message (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307FD823E37A FOREIGN KEY (section_id) REFERENCES parameter_group (id)');
        $parameterGroupId = $this->connection->fetchOne('SELECT Max(id) FROM `parameter_group`');
        $parameterGroup = [
            'id' => ++$parameterGroupId,
            'name' => 'BIKE_RIDE_TYPE',
            'label' => 'Type d\'événement',
            'role' => 'NONE',
        ];
        $this->addSql('INSERT INTO `parameter_group`(`id`, `name`, `label`, `role`) VALUES (:id, :name, :label, :role)', $parameterGroup);

        $messages = [
            [
                'id' => 1,
                'name' => 'EMAIL_CONFIRMATION_SESSION_REGISTRATION_BIKE_RIDE',
                'label' => 'Envoie mail suite à l\'inscription à une sortie VTT',
                'content' => '<p>Votre inscription &agrave; la sortie {{ bikeRideTitleAndPeriod }} a bien &eacute;t&eacute; prise en compte.</p><p>Nous vous rappelons que pour participer &agrave; une sortie, il est imp&eacute;ratif d&#39;avoir un VTT en parfait &eacute;tat de fonctionnement, un casque, une paire de gants et une paire de lunettes.</p>',
                'levelType' => Level::TYPE_ADULT_MEMBER,
                'protected' => 1,
            ],
            [
                'id' => 2,
                'name' => 'EMAIL_CONFIRMATION_SESSION_REGISTRATION_SCHOLL',
                'label' => 'Envoie mail suite à l\'inscription à une sortie L\'école VTT',
                'content' => '<p>Votre inscription &agrave; la sortie {{ bikeRideTitleAndPeriod }} a bien &eacute;t&eacute; prise en compte.</p><p>Nous vous rappelons que pour participer &agrave; une sortie, il est imp&eacute;ratif d&#39;avoir un VTT en parfait &eacute;tat de fonctionnement, un casque, une paire de gants et une paire de lunettes.</p> <p>En cas d&#39;&eacute;quipement incomplet ou de VTT en mauvais &eacute;tat, l&#39;Encadrant pourra refuser, pour des raisons de s&eacute;curit&eacute;, de prendre en charge un jeune dans son groupe. Les parents seront alors contact&eacute;s afin de venir r&eacute;cup&eacute;rer leur enfant.</p>',
                'levelType' => Level::TYPE_SCHOOL_MEMBER,
                'protected' => 0,
            ],
            [
                'id' => 3,
                'name' => 'EMAIL_CONFIRMATION_SESSION_REGISTRATION_FRAMER',
                'label' => 'Envoie mail suite à l\'inscription à une sortie pour les encadrant',
                'content' => '<p>Votre disponibilit&eacute; &agrave; la sortie {{ bikeRideTitleAndPeriod }} a bien &eacute;t&eacute; prise en compte.</p>',
                'levelType' => Level::TYPE_FRAME,
                'protected' => 0,
            ],
            [
                'id' => 4,
                'name' => 'EMAIL_CONFIRMATION_SESSION_REGISTRATION_MEETING',
                'label' => 'Envoie mail suite à l\'inscription à une réunion',
                'content' => '<p>Votre inscription &agrave; la réunion {{ bikeRideTitleAndPeriod }} a bien &eacute;t&eacute; prise en compte.</p>',
                'levelType' => null,
                'protected' => 0,
            ],
        ];

        foreach($messages as $message) {
            $message['sectionId'] = $parameterGroupId;
            $this->addSql('INSERT INTO `message`(`id`, `section_id`, `name`, `label`, `content`, `level_type`, `protected`) VALUES (:id, :sectionId, :name, :label, :content, :levelType, :protected)', $message);
        }

        $this->addSql('DELETE FROM parameter WHERE `name` IN(\'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT\',\'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR\',\'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER\')');
        $bikeRideTypes = $this->connection->fetchAllAssociative('SELECT * FROM `bike_ride_type`');
        foreach($bikeRideTypes as $bikeRideType) {
            $messageId = (str_contains($bikeRideType['name'], 'Réunion'))  ? 4 : 1;
            $this->addSql('INSERT INTO `bike_ride_type_message`(`bike_ride_type_id`, `message_id`) VALUES (:bikeRideTypeId, :messageId)', ['bikeRideTypeId' => $bikeRideType['id'], 'messageId' => $messageId]);
            if (RegistrationEnum::SCHOOL === $bikeRideType['registration']) {
                $this->addSql('INSERT INTO `bike_ride_type_message`(`bike_ride_type_id`, `message_id`) VALUES (:bikeRideTypeId, :messageId)', ['bikeRideTypeId' => $bikeRideType['id'], 'messageId' => 2]);
                $this->addSql('INSERT INTO `bike_ride_type_message`(`bike_ride_type_id`, `message_id`) VALUES (:bikeRideTypeId, :messageId)', ['bikeRideTypeId' => $bikeRideType['id'], 'messageId' => 3]);
            }
        }
        $this->addSql('UPDATE `parameter_group` SET `label`=\'Contenu\' WHERE `name` = \'CONTENT\'');
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) SELECT `parameter_group_id`, `name`, `label`, `value`, null,  1 FROM `parameter` WHERE `type` = :type', ['type' => Parameter::TYPE_HTML]);
        $this->addSql('INSERT INTO `message`(`section_id`, `name`, `label`, `content`, `level_type`, `protected`) SELECT `parameter_group_id`, `name`, `label`, `value`, null,  1 FROM `parameter` WHERE `type` = :type AND `parameter_group_id` =:parameterGroup', ['type' => Parameter::TYPE_TEXT, 'parameterGroup' => 3]);

        $this->addSql('DELETE FROM `parameter` WHERE `type` = :type', ['type' => Parameter::TYPE_HTML]);
        $this->addSql('DELETE FROM `parameter` WHERE `type` = :type AND `parameter_group_id` =:parameterGroup', ['type' => Parameter::TYPE_TEXT, 'parameterGroup' => 3]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride_type_message DROP FOREIGN KEY FK_68B5B8DEC9743080');
        $this->addSql('ALTER TABLE bike_ride_type_message DROP FOREIGN KEY FK_68B5B8DE537A1329');
        $this->addSql('ALTER TABLE message DROP FOREIGN KEY FK_B6BD307FD823E37A');
        $this->addSql('DROP TABLE bike_ride_type_message');
        $this->addSql('DROP TABLE message');
        $this->addSql('DELETE FROM parameter_group WHERE `name` =\'BIKE_RIDE_TYPE\'');
    }
}
