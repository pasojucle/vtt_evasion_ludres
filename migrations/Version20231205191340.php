<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231205191340 extends AbstractMigration
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
                'name' => 'NEW_SEASON_RE_REGISTRATION_ENABLED',
                'label' => 'Autoriser les ré-inscriptions pour {{ saison_actuelle }}',
                'type' => Parameter::TYPE_BOOL,
                'value' => 1,
                'parameterGroupName' => 'REGISTRATION'
            ],
            [
                'name' => 'NEW_SEASON_RE_REGISTRATION_DISABLED_MESSAGE',
                'label' => 'Message à afficher en cas de cloture des réinscriptions pour {{ saison_actuelle }}',
                'type' => Parameter::TYPE_TEXT,
                'value' => 'Votre licence est valable jusqu\'au 31 décembre. Vous serrez averti, lorsque vous pourrez vous inscrire pour la saison {{ saison_actuelle }}',
                'parameterGroupName' => 'REGISTRATION'
            ],
            [
                'name' => 'NEW_SEASON_RE_REGISTRATION_ENABLED_MESSAGE',
                'label' => 'Message à afficher en cas d\'ouverture des réinscriptions pour {{ saison_actuelle }}',
                'type' => Parameter::TYPE_TEXT,
                'value' => 'Les inscriptions pour la saison {{ saison_actuelle }} sont ouvertes. Vous pouvez renouveller votre licence',
                'parameterGroupName' => 'REGISTRATION'
            ],
        ];
        foreach ($parameters as $parameter) {
            $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, (SELECT `id` FROM `parameter_group` WHERE name LIKE :parameterGroupName))', $parameter);
        }
        $parameters = [
            [
                'name' => 'REQUIREMENT_SEASON_LICENCE_MESSAGE',
                'label' => 'Message si l\'inscription pour {{ saison_actuelle }} est requise pour l\'inscription à sortie'
            ],
            [
                'name' => 'REQUIREMENT_SEASON_LICENCE_AT',
                'label' => 'Date à laquelle la licence {{ saison_actuelle }} est requise pour s\'incrire à une sortie'
            ]
        ];
        foreach ($parameters as $parameter) {
            $this->addSql('UPDATE `parameter` SET `label`=:label WHERE `name`=:name', $parameter);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
