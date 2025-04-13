<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250412163652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE user_permission (permission ENUM(\'bike_ride_cluster\', \'bike_ride\', \'user\', \'product\', \'survey\', \'notification\', \'second_hand\', \'permission\', \'documentation\', \'slideshow\', \'participation\', \'summary\') NOT NULL COMMENT \'(DC2Type:Permission)\', user_id INT NOT NULL, INDEX IDX_472E5446A76ED395 (user_id), PRIMARY KEY(user_id, permission)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE user_permission ADD CONSTRAINT FK_472E5446A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $users = $this->connection->executeQuery('SELECT `id`, `permissions` FROM `user`')->fetchAllAssociative();
        foreach ($users as $user) {
            $permissions = ($user['permissions']) ? json_decode($user['permissions'], true) : [];
            foreach($permissions as $permission => $value) {
                if (true === $value) {
                    $this->connection->executeQuery('INSERT INTO user_permission (user_id, permission) VALUES (:user_id, :permission)', ['user_id' => $user['id'], 'permission' => strtolower($permission)]);
                }
            }
        }


        //$this->addSql('ALTER TABLE user DROP permissions');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE user_permission DROP FOREIGN KEY FK_472E5446A76ED395');
        // $this->addSql('DROP TABLE user_permission');
        // $this->addSql('ALTER TABLE user ADD permissions JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }
}
