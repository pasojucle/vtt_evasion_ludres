<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221211125823 extends AbstractMigration
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
                'name' => 'REQUIREMENT_SEASON_LICENCE_AT',
                'label' => 'Date à laquelle la licence de la nouvelle saison est obligatoire pour s\'incrire à une sortie',
                'content' => '{"day":"1","month":"1"}',
                'type' => Parameter::TYPE_MONTH_AND_DAY,
                'parameterGroup' => 3,
            ],
            [
                'name' => 'REQUIREMENT_SEASON_LICENCE_MESSAGE',
                'label' => 'Message si l\'inscription de la nouvelle saison est requise pour l\'inscription à sortie ',
                'content' => '<span>Pour vous inscrire à une sortie, vous devez transmettre au club votre dossier d\'inscription pour la saison {{ saison_actuelle }}.</span>',
                'type' => Parameter::TYPE_TEXT,
                'parameterGroup' => 2,
            ],
        ];
        
        foreach($parameters as $parameter) {
            $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :content, :parameterGroup)', $parameter);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
