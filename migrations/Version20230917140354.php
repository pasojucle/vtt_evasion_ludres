<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230917140354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO `identity` (`user_id`, `address_id`, `name`, `first_name`, `birth_date`, `birthplace`, `phone`, `mobile`, `profession`, `kinship`, `email`, `picture`, `birth_department`, `type`, `birth_commune_id`, `emergency_phone`) VALUES
        (1, 110, \'Boulangé\', \'Patrick\', \'1971-09-09\', \'\', NULL, \'0635414473\', NULL, NULL, \'pasojucle@gmail.com\', NULL, NULL, 1, NULL, \'0632595827\')');

        $this->addSql('INSERT INTO `licence` (`user_id`, `type`, `coverage`, `magazine_subscription`, `subscription_amount`, `additional_family_member`, `medical_certificate_required`, `category`, `season`, `created_at`, `final`, `status`, `current_season_form`, `is_vae`) VALUES
        (1, 2, 2, 0, NULL, 0, 0, 2, 2009, \'2009-01-01 08:00:00\', 1, 5, 0, 0)');

        $lastId = $this->connection->executeQuery('SELECT Max(id) FROM `health`')->fetchOne();

        $data = ['id' => ++$lastId];
        $this->addSql('INSERT INTO `health`(`id`, `medical_certificate_date`, `at_least_one_positve_response`) VALUES (:id, \'2009-01-01\',0)', $data);

        $this->addSql('UPDATE `user` SET `health_id`=:id,`licence_number`=\'667032\', `level_id`=11 WHERE `licence_number` LIKE \'webmaster\'', $data);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
