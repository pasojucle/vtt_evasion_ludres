<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220416151943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bike_ride_type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, content LONGTEXT DEFAULT NULL, is_registrable TINYINT(1) DEFAULT 1 NOT NULL, is_compensable TINYINT(1) DEFAULT 0 NOT NULL, is_school TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE indemnity (id INT AUTO_INCREMENT NOT NULL, level_id INT NOT NULL, bike_ride_type_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, INDEX IDX_6BBFD1CE5FB14BA7 (level_id), INDEX IDX_6BBFD1CEC9743080 (bike_ride_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE indemnity ADD CONSTRAINT FK_6BBFD1CE5FB14BA7 FOREIGN KEY (level_id) REFERENCES level (id)');

        $bikeRideTypes = [
            'EVENT_CASUAL' => ['id' => 1, 'name' => 'Randonnée occasionnelle', 'content' => null, 'isCompensable' => 0, 'isRegistrable' => 1, 'isSchool' => 0],
            'EVENT_SCHOOL_CONTENT' => ['id' => 2, 'isCompensable' => 1, 'isRegistrable' => 1, 'isSchool' => 1],
            'EVENT_ADULT_CONTENT' => ['id' => 3,'isCompensable' => 0, 'isRegistrable' => 1, 'isSchool' => 0],
            'EVENT_HOLIDAYS_CONTENT' => ['id' => 4,'isCompensable' => 0, 'isRegistrable' => 0, 'isSchool' => 0],
            'EVENT_CRITERIUM' => ['id' => 5, 'name' => 'Criterium', 'content' => null, 'isCompensable' => 1, 'isRegistrable' => 1, 'isSchool' => 0],
        ];
        
        $bikeRideParameters = $this->connection->fetchAllAssociative('SELECT * FROM `parameter` WHERE `name`in (\'EVENT_SCHOOL_CONTENT\', \'EVENT_ADULT_CONTENT\', \'EVENT_HOLIDAYS_CONTENT\')');
        foreach ($bikeRideParameters as $bikeRideParameter) {
            $bikeRideTypes[$bikeRideParameter['name']]['name'] = $bikeRideParameter['label'];
            $bikeRideTypes[$bikeRideParameter['name']]['content'] = $bikeRideParameter['value'];
        }
        foreach ($bikeRideTypes as $bikeRideType) {
            $this->addSql('INSERT INTO `bike_ride_type`(`id`, `name`, `content`, `is_compensable`, `is_registrable`, `is_school`) VALUES (:id, :name, :content, :isCompensable, :isRegistrable, :isSchool)', $bikeRideType);
        }
        $this->addSql('DELETE FROM `parameter` WHERE `name`in (\'EVENT_SCHOOL_CONTENT\', \'EVENT_ADULT_CONTENT\', \'EVENT_HOLIDAYS_CONTENT\')');
        $this->addSql('DELETE FROM `parameter_group` WHERE `name` = \'BIKE_RIDE\'');
        $this->addSql('ALTER TABLE bike_ride ADD bike_ride_type_id INT');
        $this->addSql('ALTER TABLE indemnity ADD CONSTRAINT FK_6BBFD1CEC9743080 FOREIGN KEY (bike_ride_type_id) REFERENCES bike_ride_type (id)');
        $this->addSql('ALTER TABLE bike_ride ADD CONSTRAINT FK_8A8A7B3CC9743080 FOREIGN KEY (bike_ride_type_id) REFERENCES bike_ride_type (id)');
        $this->addSql('CREATE INDEX IDX_8A8A7B3CC9743080 ON bike_ride (bike_ride_type_id)');
        $this->addSql('UPDATE `bike_ride` SET `bike_ride_type_id` = `type`');
        $this->addSql('ALTER TABLE bike_ride CHANGE bike_ride_type_id bike_ride_type_id INT NOT NULL');
        $this->addSql('ALTER TABLE bike_ride DROP `type`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE bike_ride ADD `type` INT NOT NULL');
        $this->addSql('UPDATE `bike_ride` SET `type` = `bike_ride_type_id`');
        $this->addSql('ALTER TABLE bike_ride DROP FOREIGN KEY FK_8A8A7B3CC9743080');
        $this->addSql('ALTER TABLE indemnity DROP FOREIGN KEY FK_6BBFD1CEC9743080');
        $this->addSql('DROP TABLE bike_ride_type');
        $this->addSql('DROP TABLE indemnity');
        $this->addSql('DROP INDEX IDX_8A8A7B3CC9743080 ON bike_ride');
        $this->addSql('ALTER TABLE bike_ride DROP bike_ride_type_id');
        $this->addSql("INSERT INTO `parameter_group` (`id`, `name`, `label`, `role`) VALUES (1, 'BIKE_RIDE', 'Sorties', 'ROLE_ADMIN');");
        $this->addSql("INSERT INTO `parameter` (`id`, `name`, `label`, `type`, `value`, `parameter_group_id`) VALUES
        (1, 'EVENT_SCHOOL_CONTENT', 'ÉcoleVTT', 1, 'Ecole VTT sur Ludres de 14h00 précise à 17h00, école VTT, rando et goûter.', 1),
        (2, 'EVENT_ADULT_CONTENT', 'Rando adultes et ados', 1, '1er groupe (40 à 50 km) rendez-vous à 8h30 sur le parking de la Jauffaite, en face de l\'école Jacques Prévert ou à 9h00 au club sur le plateau de Ludres.2e et 3e groupe (20 à 40 km). Rendez-vous à 9h00 au club sur le plateau.', 1),
        (12, 'EVENT_HOLIDAYS_CONTENT', 'ÉcoleVTT: Vacances scolaires', 1, 'Il n\'y aura pas de séances d\'école VTT les samedis 30 octobre et 06 novembre. Reprise le samedi 13 novembre.\r\n\r\nBonnes vacances à toutes et à tous', 1);");
    }
}
