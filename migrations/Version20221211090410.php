<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221211090410 extends AbstractMigration
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
                'content' => '<p>Votre inscription à la sortie {{ bikeRideTitleAndPeriod }} a bien été prise en compte.</p><p>Nous vous rappellons que pour participer à une sortie, il est impératif d\'avoir un VTT en parfait état de fonctionnement, un casque, une paire de gants et une paire de lunettes.</p>',
                'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_ADULT',
                'label' => 'Envoie mail Suite à l\'inscription à une sortie pour les adultes',
                'type' => Parameter::TYPE_TEXT,
                'parameterGroup' => 2,
            ],
            [
                'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_MINOR',
                'label' => 'Envoie mail Suite à l\'inscription à une sortie pour les mineur',
                'content' => '<p>Votre inscription à la sortie {{ bikeRideTitleAndPeriod }} a bien été prise en compte.</p><p>Nous vous rappellons que pour participer à une sortie, il est impératif d\'avoir un VTT en parfait état de fonctionnement, un casque, une paire de gants et une paire de lunettes.</p><p>En cas d\'équipement incomplet ou de VTT en mauvais état, l\'Encadrant pourra refuser, pour des raisons de sécurité, de prendre en charge un jeune dans son groupe. Les parents seront alors contactés afin de venir récupérer leur enfant.</p>',
                'type' => Parameter::TYPE_TEXT,
                'parameterGroup' => 2,
            ],
            [
                'name' => 'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION_FRAMER',
                'label' => 'Envoie mail Suite à l\'inscription à une sortie pour les encadrant',
                'content' => '<p>Votre disponibilité à la sortie {{ bikeRideTitleAndPeriod }} a bien été prise en compte.</p>',
                'type' => Parameter::TYPE_TEXT,
                'parameterGroup' => 2,
            ],
        ];
        foreach($parameters as $parameter) {
            $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :content, :parameterGroup)', $parameter);
        }
        $this->addSql('DELETE FROM `parameter` WHERE `name` = \'EMAIL_ACKNOWLEDGE_SESSION_REGISTRATION\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
