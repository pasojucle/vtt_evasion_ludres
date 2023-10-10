<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\User;
use App\Security\Voter\BikeRideVoter;
use App\Security\Voter\UserVoter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230928162435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD permissions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');

        $users = $this->connection->executeQuery('SELECT * FROM `user` WHERE `roles` NOT LIKE \'%_ADMIN%\'')->fetchAllAssociative();
        foreach($users as $user) {
            $permissions = [];
            $roles = ['ROLE_USER'];
            if (str_contains($user['roles'], 'ROLE_REGISTER')) {
                $permissions[User::PERMISSION_USER] = true;
            };
            if (str_contains($user['roles'], 'ROLE_FRAME')) {
                $permissions[User::PERMISSION_BIKE_RIDE] = true;
            };
            $data = [
                'id' => $user['id'],
                'roles' => json_encode($roles),
                'permissions' => json_encode($permissions),
            ];
            $this->addSql('UPDATE `user` SET `roles`=:roles, `permissions`=:permissions WHERE `id`=:id', $data);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP frame');
    }
}
