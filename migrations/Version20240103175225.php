<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240103175225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameter = [
            'name' => 'DEDUPLICATION_MAILER_ENABLED',
            'label' => 'Activer l\'envoi d\'une copie des mails',
            'type' => Parameter::TYPE_BOOL,
            'value' => 0,
            'parameterGroupName' => 'MAINTENANCE'
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $parameter);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
