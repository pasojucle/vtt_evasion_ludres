<?php

declare(strict_types=1);

namespace DoctrineMigrations;


use Doctrine\DBAL\Schema\Schema;
use App\Entity\Enum\DisplayModeEnum;
use App\Entity\Enum\LicenceOptionEnum;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251108070452 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence ADD options JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE licence set options=:options', ['options' => json_encode([LicenceOptionEnum::NO_ADDITIONAL_OPTION])]);
        $registrationStep = [
            'title' => 'Assurance',
            'content' => '<p>{{ boutons_radio_assurance }}</p><p>&nbsp;</p><p>{{ boutons_radio_options }}</p><p><strong>Les options d\'assurance sont en supplément du prix de la licence et doivent être souscrites directement en ligne via </strong><a href="https://licencie.ffcyclo.org/"><strong>votre </strong><span style="color:hsl(30,75%,60%);"><strong>espace licencié</strong></span></a><strong> de la FFVELO.</strong></p>'
        ];
        $this->connection->executeQuery('UPDATE registration_step SET content=:content WHERE title LIKE :title', $registrationStep);

        $registrationSteps = [
            [
                'title' => 'Questionnaire de santé ffvélo',
                'testingRender' => 1
            ],        
            [
                'title' => 'Questionnaire de santé ffvélo [pdf)',
                'testingRender' => 2
            ],
        ];            
        foreach ($registrationSteps as $registrationStep) {
            $this->connection->executeQuery('UPDATE registration_step SET testing_render=:testingRender WHERE title LIKE :title', $registrationStep);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence DROP options');
    }
}
