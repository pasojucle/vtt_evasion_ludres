<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220402095646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        $this->addSql('ALTER TABLE licence ADD current_season_form TINYINT(1) DEFAULT 0 NOT NULL');

        $parameters = [
            [
                'name' => 'SEASON_START_AT',
                'label' => 'Date du début de la nouvelle saison',
                'type' => Parameter::TYPE_MONTH_AND_DAY,
                'value' => json_encode(['day' => '1', 'month' => '9']),
                'groupParameterId' => 3,
            ],
            [
                'name' => 'COVERAGE_FORM_AVAILABLE_AT',
                'label' => 'Date du début de la disponibilité du bulletin d\'assurance',
                'type' => Parameter::TYPE_MONTH_AND_DAY,
                'value' => json_encode(['day' => '1', 'month' => '12']),
                'groupParameterId' => 3,
            ],
        ];
        foreach ($parameters as $parameter) {
            $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) 
            VALUES (:name, :label, :type, :value, :groupParameterId)', $parameter);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence DROP current_season_form');
    }
}
