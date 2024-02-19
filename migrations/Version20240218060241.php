<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240218060241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $parameter = [
            'name' => 'BIKE_RIDE_ABSENCE_EMAIL',
            'label' => 'Message envoyé au responsable légal en cas d\'absence à une rando de l\'école vttt',
            'type' => Parameter::TYPE_HTML,
            'value' => '<p>Votre enfant {{ prenom_nom_enfant }} n&#39;est pas pr&eacute;sent &agrave; la rando {{ nom_rando }}.</p><p>Nous vous rappelons qu&#39;en cas d&#39;impr&eacute;vu, vous devez le d&eacute;sincrire en vous connectant sur le site, onglet <a href="{{ nom_domaine }}/mon-compte/programme">Mon programme perso</a></p><p>Si cette absence vous semble anormale, vous pouvez contacter le responsable du groupe {{ nom_encadrant }} au {{ telephone_encadrant }}</p>',
            'parameterGroupName' => 'BIKE_RIDE'
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $parameter);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
