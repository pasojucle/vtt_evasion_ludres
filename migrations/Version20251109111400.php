<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251109111400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_permission CHANGE permission permission ENUM(\'bike_ride_cluster\', \'bike_ride\', \'user\', \'product\', \'survey\', \'notification\', \'second_hand\', \'permission\', \'documentation\', \'slideshow\', \'participation\', \'summary\', \'skill\') NOT NULL COMMENT \'(DC2Type:Permission)\'');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('DELETE FROM user_permission WHERE user_id=:user', ['user' => 79]);
    }

    public function preDown(Schema $schema): void
    {
        $this->connection->executeQuery('DELETE FROM user_permission WHERE permission=:permission', ['permission' => 'skill']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_permission CHANGE permission permission ENUM(\'bike_ride_cluster\', \'bike_ride\', \'user\', \'product\', \'survey\', \'notification\', \'second_hand\', \'permission\', \'documentation\', \'slideshow\', \'participation\', \'summary\') NOT NULL COMMENT \'(DC2Type:Permission)\'');
    }
}
