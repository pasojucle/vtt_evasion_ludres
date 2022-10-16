<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221016160933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $parameterGroupId = $this->connection->fetchOne('SELECT id FROM `parameter_group` WHERE `name` = \'EMAIL\'');
        dump($parameterGroupId);
        if (false !== $parameterGroupId) {
            $parameterGroup = ['id' => $parameterGroupId, 'name' => 'MESSAGES', 'label' => 'Messages'];
            $this->addSql('UPDATE `parameter_group` SET `name` = :name, `label` = :label WHERE `id` = :id', $parameterGroup);

            $parameter = ['name' => 'MODAL_WINDOW_ORDER_IN_PROGRESS', 'parameterGroupId' => $parameterGroupId];
            $this->addSql('UPDATE `parameter` SET `parameter_group_id` = :parameterGroupId WHERE `name` = :name', $parameter);
        }

        $this->addSql('DELETE FROM `parameter_group` WHERE `name` = \'MODAL_WINDOW\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
