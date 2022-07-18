<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220718172113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameterGroupeId = $this->connection->fetchOne('SELECT `id` FROM parameter_group WHERE `name` = \'EMAIL\'');
        $parameter = [
            'parameterGroupeId' => $parameterGroupeId,
            'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION',
            'label' => 'Envoie mail Suite à l\'inscription à une sortie',
            'type' => Parameter::TYPE_TEXT,
            'value' => '<p>Votre inscription à la sortie {{ bikeRideTitleAndPeriod }} a bien été prise en compte.</p><p>Nous vous rapellons que pour participer à une sortie, il est impératif d\'avoir un VTT en parfait état de fonctionnement, un casque, une paire de gants et une paire de lunettes.</p><p>En cas d\'équipement incomplet ou de VTT en mauvais état, l\'Encadrant pourra refuser, pour des raisons de sécurité, de prendre en charge un jeune dans son groupe. Les parents seront alors contactés afin de venir récupérer leur enfant.</p>',
        ];
        $this->addSql("INSERT INTO `parameter` (`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES
        (:name, :label, :type, :value, :parameterGroupeId)", $parameter);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
