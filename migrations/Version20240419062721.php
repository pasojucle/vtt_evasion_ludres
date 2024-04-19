<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240419062721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameterGroup = [
            'name' => 'SLIDESHOW',
            'label' => 'Diaporama',
            'role' => 'NONE',
        ];
        $this->addSql('INSERT INTO `parameter_group`(`name`, `label`, `role`) VALUES (:name, :label, :role)', $parameterGroup);

        $parameter = [
            'name' => 'SLIDESHOW_MAX_DISK_SIZE',
            'label' => 'Espace disque alloué pour le diaporama',
            'type' => Parameter::TYPE_TEXT,
            'value' => '20G',
            'parameterGroupName' => 'SLIDESHOW'
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $parameter);
    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DELETE FROM `parameter` WHERE `name`LIKE \'SLIDESHOW_MAX_DISK_SIZE\'');
        $this->addSql('DELETE FROM `parameter_group` WHERE  `name`LIKE \'SLIDESHOW\'');
    }
}
