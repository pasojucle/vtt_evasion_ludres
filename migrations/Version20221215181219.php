<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221215181219 extends AbstractMigration
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
                'name' => 'HIKE_MEDICAL_CERTIFICATE_DURATION',
                'label' => 'Durée du certificat médical pour une licence "Rando"',
                'content' => 5,
                'type' => Parameter::TYPE_INTEGER,
                'parameterGroup' => 3,
            ],
            [
                'name' => 'SPORT_MEDICAL_CERTIFICATE_DURATION',
                'label' => 'Durée du certificat médical pour une licence "Sport"',
                'content' => 3,
                'type' => Parameter::TYPE_INTEGER,
                'parameterGroup' => 3,
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
