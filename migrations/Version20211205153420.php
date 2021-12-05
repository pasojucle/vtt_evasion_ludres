<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\RegistrationStep;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211205153420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_step_group (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(50) NOT NULL, order_by INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_step ADD registration_step_group_id INT DEFAULT NULL, ADD final_render INT NOT NULL, DROP to_pdf');
        $this->addSql('ALTER TABLE registration_step ADD CONSTRAINT FK_8C87E9E052FE2359 FOREIGN KEY (registration_step_group_id) REFERENCES registration_step_group (id)');
        $this->addSql('CREATE INDEX IDX_8C87E9E052FE2359 ON registration_step (registration_step_group_id)');
        
        $registrationStepGroups = [
            ['id' => 1, 'title' => 'Informations',],
            ['id' => 2, 'title' => 'Tableaux des garanties',],
            ['id' => 3, 'title' => 'Tarifs',],
            ['id' => 4, 'title' => 'Assurance',],
            ['id' => 5, 'title' => 'Autorisations',],
            ['id' => 6, 'title' => 'Santé',],
            ['id' => 7, 'title' => 'Validation',],
            ['id' => 8, 'title' => 'Téléchargement',],
        ];
        foreach($registrationStepGroups as $registrationStepGroup) {
            $registrationStepGroup['orderBy'] = $registrationStepGroup['id'] - 1;
            $this->addSql('INSERT INTO `registration_step_group`(`id`, `title`, `order_by`) VALUES (:id, :title, :orderBy)', $registrationStepGroup);
        }
        
        $registrationSteps = [
            [
                'registrationStepGroup' => 1,
                'title' => 'Informations de l\'adhérent',
                'testingRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 1,
                'title' => 'Informations du parent ou tuteur de l\'enfant',
                'testingRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 2,
            ],
            [
                'registrationStepGroup' => 2,
                'title' => 'Tableaux des garanties',
                'testingRender' => RegistrationStep::RENDER_NONE,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 3,
                'title' => 'Tarifs',
                'testingRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 4,
                'title' => 'Assurance',
                'testingRender' => RegistrationStep::RENDER_FILE,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 5,
                'title' => 'Droit image',
                'newTitle' => 'Autorisations adulte',
                'testingRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 5,
                'title' => 'Autorisations',
                'newTitle' => 'Autorisations mineur',
                'testingRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 6,
                'title' => 'Questionnaire de santé adulte',
                'testingRender' => RegistrationStep::RENDER_NONE,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 6,
                'title' => 'Questionnaire de santé mineur',
                'testingRender' => RegistrationStep::RENDER_NONE,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 6,
                'title' => 'Santé de l\'enfant',
                'testingRender' => RegistrationStep::RENDER_NONE,
                'finalRender' => RegistrationStep::RENDER_FILE_AND_VIEW,
                'orderBy' => 2,
            ],
            [
                'registrationStepGroup' => 7,
                'title' => 'Validation',
                'testingRender' => RegistrationStep::RENDER_VIEW,
                'finalRender' => RegistrationStep::RENDER_VIEW,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 8,
                'title' => 'Téléchargement',
                'testingRender' => RegistrationStep::RENDER_VIEW,
                'finalRender' => RegistrationStep::RENDER_VIEW,
                'orderBy' => 1,
            ],
        ];
        foreach($registrationSteps as $registrationStep) {
            if (!array_key_exists('newTitle', $registrationStep)) {
                $registrationStep['newTitle'] = $registrationStep['title'];
            }
            $this->addSql('UPDATE `registration_step` SET 
                `registration_step_group_id` = :registrationStepGroup,
                `title` = :newTitle,
                `testing_render` = :testingRender,
                `final_render` = :finalRender,
                `order_by` = :orderBy
                WHERE `title` = :title', $registrationStep);
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_step DROP FOREIGN KEY FK_8C87E9E052FE2359');
        $this->addSql('DROP TABLE registration_step_group');
        $this->addSql('DROP INDEX IDX_8C87E9E052FE2359 ON registration_step');
        $this->addSql('ALTER TABLE registration_step ADD to_pdf TINYINT(1) NOT NULL, DROP registration_step_group_id, DROP final_render');
    }
}
