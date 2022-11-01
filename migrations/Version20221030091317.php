<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221030091317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE disease_kind (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, category INT NOT NULL, bike_ride_alert TINYINT(1) DEFAULT 0 NOT NULL, custom_label TINYINT(1) DEFAULT 0 NOT NULL, emergency_treatment TINYINT(1) DEFAULT 0 NOT NULL, deleted TINYINT(1) DEFAULT 0 NOT NULL, order_by INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE disease ADD disease_kind_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE disease ADD CONSTRAINT FK_F3B6AC1310FF969 FOREIGN KEY (disease_kind_id) REFERENCES disease_kind (id)');
        $this->addSql('CREATE INDEX IDX_F3B6AC1310FF969 ON disease (disease_kind_id)');

        $diseaseKinds = [
            1 => ['name' => 'Enurésie', 'category' => 1, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 0],
            2 => ['name' => 'Tétanie', 'category' => 1, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 1],
            3 => ['name' => 'Asthme', 'category' => 1, 'bikeRideAlert' => 1, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 2],
            4 => ['name' => 'Hémophilie', 'category' => 1, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 3],
            5 => ['name' => 'Epilepsie', 'category' => 1, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 4],
            6 => ['name' => 'Diabète', 'category' => 1, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 5],
            7 => ['name' => 'Autres', 'category' => 1, 'bikeRideAlert' => 0, 'customLabel' => 1, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 6],
            8 => ['name' => 'Alimentaires', 'category' => 2, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 0],
            9 => ['name' => 'Médicamenteuses', 'category' => 2, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 1],
            10 => ['name' => 'Pollen, Abeilles', 'category' => 2, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 1, 'deleted' => 0, 'orderBy' => 3],
            11 => ['name' => 'Aux aliments', 'category' => 3, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 0, 'deleted' => 0, 'orderBy' => 0], 
            12 => ['name' => 'Aux médicaments', 'category' => 3, 'bikeRideAlert' => 0, 'customLabel' => 0, 'emergencyTreatment' => 0, 'deleted' => 0, 'orderBy' => 1],
        ];

        foreach ($diseaseKinds as $key => $diseaseKind) {
            $diseaseKind['id'] = $key;
            $this->addSql('INSERT INTO `disease_kind`(`id`, `name`, `category`, `bike_ride_alert`, `custom_label`, `emergency_treatment`, `order_by`) VALUES (:id, :name, :category, :bikeRideAlert, :customLabel, :emergencyTreatment, :orderBy)', $diseaseKind);
        }

        $this->addSql('UPDATE `disease` SET `disease_kind_id` = `label`');
        $this->addSql('ALTER TABLE disease DROP `label`, DROP `type`');
        $this->addSql('UPDATE `disease` SET `curent_treatment`= null WHERE  LOWER(`curent_treatment`) = \'non\'');
        $this->addSql('UPDATE `disease` SET `emergency_treatment`= null WHERE  LOWER(`emergency_treatment`) = \'non\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `disease` ADD type INT NOT NULL, ADD label INT NOT NULL');
        $this->addSql('UPDATE `disease` SET `label` = `disease_kind_id`');
        $this->addSql('UPDATE `disease` SET `type` = 1 WHERE `disease_kind_id` < 8');
        $this->addSql('UPDATE `disease` SET `type` = 2 WHERE `disease_kind_id` BETWEEN 8 AND 10');
        $this->addSql('UPDATE `disease` SET `type` = 3 WHERE `disease_kind_id` > 1');

        $this->addSql('ALTER TABLE disease DROP FOREIGN KEY FK_F3B6AC1310FF969');
        $this->addSql('DROP TABLE disease_kind');
        $this->addSql('DROP INDEX IDX_F3B6AC1310FF969 ON disease');
        $this->addSql('ALTER TABLE disease DROP disease_kind_id, DROP category');

    }
}
