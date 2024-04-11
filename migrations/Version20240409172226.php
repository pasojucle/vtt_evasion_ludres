<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240409172226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride ADD registration_enabled TINYINT(1) DEFAULT 1 NOT NULL');
        $parameter = [
            'name' => 'BIKE_RIDE_CAN_UNSUBSCRIBE_MESSAGE',
            'label' => 'Message afficher lorsque la désinscription n\'est plus possible',
            'type' => Parameter::TYPE_HTML,
            'value' => '<p>Pour vous désinscrire, vous devez contacter un responsable du club</p>',
            'parameterGroupName' => 'BIKE_RIDE'
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $parameter);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bike_ride DROP registration_enabled');
        $this->addSql('DELETE FROM `parameter` WHERE `name`=\'BIKE_RIDE_CAN_UNSUBSCRIBE_MESSAGE\'');
    }
}
