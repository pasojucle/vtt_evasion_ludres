<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240108182736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameters = [
            [
                'name' => 'MODAL_WINDOW_ORDER_IN_PROGRESS',
                'parameterGroupName' => 'ORDER'
            ],
            [
                'name' => 'MODAL_WINDOW_REGISTRATION_IN_PROGRESS',
                'parameterGroupName' => 'REGISTRATION'
            ],
            [
                'name' => 'EMAIL_LICENCE_VALIDATE',
                'parameterGroupName' => 'USER'
            ],
        ];

        foreach($parameters as $parameter) {
            $this->addSql('UPDATE `parameter` SET`parameter_group_id`= (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName) WHERE `name` = :name', $parameter);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
