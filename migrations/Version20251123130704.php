<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\RegistrationFormEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251123130704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE agreement (id VARCHAR(25) NOT NULL, title VARCHAR(50) NOT NULL, content LONGTEXT NOT NULL, category ENUM(\'school\', \'adult\', \'school_and_adult\') NOT NULL COMMENT \'(DC2Type:LicenceCategory)\', membership ENUM(\'trial\', \'yearly\', \'trial_and_yearly\') NOT NULL COMMENT \'(DC2Type:LicenceMembership)\', registration_form ENUM(\'none\', \'registration_document\', \'health_question\', \'identity\', \'health\', \'licence_agreements\', \'licence_coverage\', \'membership_fee\', \'registration_file\', \'overview\', \'member\', \'gardians\') NOT NULL COMMENT \'(DC2Type:RegistrationForm)\', kind ENUM(\'authorization\', \'consent\') NOT NULL COMMENT \'(DC2Type:AgreementKind)\', authorization_message VARCHAR(50) DEFAULT NULL, rejection_message VARCHAR(50) DEFAULT NULL, authorization_icon VARCHAR(50) DEFAULT NULL, rejection_icon VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licence_agreement (id INT AUTO_INCREMENT NOT NULL, licence_id INT DEFAULT NULL, agreement_id VARCHAR(25) DEFAULT NULL, agreed TINYINT(1) NOT NULL, INDEX IDX_9A3C424626EF07C9 (licence_id), INDEX IDX_9A3C424624890B2B (agreement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_step_agreement (registration_step_id INT NOT NULL, agreement_id VARCHAR(25) NOT NULL, INDEX IDX_D4C6CDBD33C34B3A (registration_step_id), INDEX IDX_D4C6CDBD24890B2B (agreement_id), PRIMARY KEY(registration_step_id, agreement_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE licence_agreement ADD CONSTRAINT FK_9A3C424626EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_agreement ADD CONSTRAINT FK_9A3C424624890B2B FOREIGN KEY (agreement_id) REFERENCES agreement (id)');
        $this->addSql('ALTER TABLE registration_step_agreement ADD CONSTRAINT FK_D4C6CDBD33C34B3A FOREIGN KEY (registration_step_id) REFERENCES registration_step (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE registration_step_agreement ADD CONSTRAINT FK_D4C6CDBD24890B2B FOREIGN KEY (agreement_id) REFERENCES agreement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE licence_consent DROP FOREIGN KEY FK_ADA5C2A341079D63');
        $this->addSql('ALTER TABLE licence_consent DROP FOREIGN KEY FK_ADA5C2A326EF07C9');
        $this->addSql('ALTER TABLE licence_authorization DROP FOREIGN KEY FK_5E02D72726EF07C9');
        $this->addSql('ALTER TABLE licence_authorization DROP FOREIGN KEY FK_5E02D7272F8B0EB2');
        $this->addSql('ALTER TABLE registration_step ADD trial_display_mode ENUM(\'none\', \'screen\', \'file\', \'screen_and_file\', \'file_and_link\') NOT NULL COMMENT \'(DC2Type:DisplayMode)\', ADD yearly_display_mode ENUM(\'none\', \'screen\', \'file\', \'screen_and_file\', \'file_and_link\') NOT NULL COMMENT \'(DC2Type:DisplayMode)\', ADD form_enum ENUM(\'none\', \'registration_document\', \'health_question\', \'identity\', \'health\', \'licence_agreements\', \'licence_coverage\', \'membership_fee\', \'registration_file\', \'overview\', \'member\', \'gardians\') NOT NULL COMMENT \'(DC2Type:RegistrationForm)\', CHANGE order_by order_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE consent ADD form_enum ENUM(\'none\', \'registration_document\', \'health_question\', \'identity\', \'health\', \'licence_agreements\', \'licence_coverage\', \'membership_fee\', \'registration_file\', \'overview\', \'member\', \'gardians\') NOT NULL COMMENT \'(DC2Type:RegistrationForm)\'');
    }

    public function postUp(Schema $schema): void
    {
        $formToEnum = [
            0 => RegistrationFormEnum::REGISTRATION_DOCUMENT->value,
            1 => RegistrationFormEnum::HEALTH_QUESTION->value,
            2 => RegistrationFormEnum::IDENTITY->value,
            3 => RegistrationFormEnum::HEALTH->value,
            4 => RegistrationFormEnum::LICENCE_AGREEMENTS->value,
            5 => RegistrationFormEnum::LICENCE_COVERAGE->value,
            6 => RegistrationFormEnum::MEMBERSHIP_FEE->value,
            7 => RegistrationFormEnum::REGISTRATION_FILE->value,
            9 => RegistrationFormEnum::OVERVIEW->value,
            10 => RegistrationFormEnum::MEMBER->value,
            11 => RegistrationFormEnum::GARDIANS->value,
        ];

        $registrationStepForms = $this->connection->fetchAllAssociative('SELECT form FROM registration_step GROUP BY form');
        foreach($registrationStepForms as $registrationStepForm) {
            $registrationStepForm['formEnum'] = $formToEnum[$registrationStepForm['form']];
            $this->connection->executeQuery('UPDATE registration_step SET form_enum=:formEnum WHERE form=:form', $registrationStepForm);
        }
        $this->connection->executeQuery('ALTER TABLE registration_step DROP form');
        $this->connection->executeQuery('ALTER TABLE registration_step CHANGE form_enum form ENUM(\'none\', \'registration_document\', \'health_question\', \'identity\', \'health\', \'licence_agreements\', \'licence_coverage\', \'membership_fee\', \'registration_file\', \'overview\', \'member\', \'gardians\') NOT NULL COMMENT \'(DC2Type:RegistrationForm)\'');

        $consentForms = $this->connection->fetchAllAssociative('SELECT registration_form FROM consent GROUP BY registration_form');
        foreach($consentForms as $consentForm) {
            $consentForm['formEnum'] = $formToEnum[$consentForm['registration_form']];
            $this->connection->executeQuery('UPDATE consent SET form_enum=:formEnum WHERE registration_form=:registration_form', $consentForm);
        }

        $this->connection->executeQuery('INSERT INTO `agreement`(`id`, `title`, `content`, `category`, `membership`, `registration_form`, `kind`, `authorization_message`, `rejection_message`, `authorization_icon`, `rejection_icon`) 
        SELECT 
            a.id,
            a.title,
            a.content,
            a.category,
            a.membership,
            :registrationForm,
            :kind,
            a.authorization_message,
            a.rejection_message,
            a.authorization_icon,
            a.rejection_icon
        FROM `authorization` a', [
            'kind' => AgreementKindEnum::AUTHORIZATION->value,
            'registrationForm' => RegistrationFormEnum::LICENCE_AGREEMENTS->value,
        ]);   

        $this->connection->executeQuery('INSERT INTO `agreement`(`id`, `title`, `content`, `category`, `membership`, `registration_form`, `kind`) 
        SELECT 
            c.id,
            c.title,
            c.content,
            c.category,
            c.membership,
            c.form_enum,
            :kind
        FROM `consent` c', ['kind' => AgreementKindEnum::CONSENT->value]);     

        $this->connection->executeQuery('INSERT INTO `registration_step_agreement`(`registration_step_id`, `agreement_id`) 
            SELECT rs.id AS registration_step_id,
            a.id  AS agreement_id
            FROM registration_step rs
            JOIN agreement a 
            ON rs.form = a.registration_form');

        $this->connection->executeQuery('INSERT INTO `licence_agreement`(`licence_id`, `agreement_id`, `agreed`) 
            SELECT 
                la.licence_id,
                la.authorization_id,
                la.value
            FROM `licence_authorization` la');

        $this->connection->executeQuery('INSERT INTO `licence_agreement`(`licence_id`, `agreement_id`, `agreed`) 
            SELECT 
                lc.licence_id,
                lc.consent_id,
                lc.value
            FROM `licence_consent` lc');
            
        foreach($this->getDisplayModes() as $displayMode) {
            $this->connection->executeQuery('UPDATE registration_step SET trial_display_mode=:displayMode WHERE testing_render=:render ', $displayMode);
            $this->connection->executeQuery('UPDATE registration_step SET yearly_display_mode=:displayMode WHERE final_render=:render ', $displayMode);
        }
        $this->connection->executeQuery('UPDATE registration_step SET content=:content WHERE id IN (18, 19)', ['content' => null]);
        
        $this->connection->executeQuery('ALTER TABLE agreement DROP registration_form');
        $this->connection->executeQuery('ALTER TABLE registration_step DROP testing_render, DROP final_render');

        $this->connection->executeQuery('DROP TABLE licence_consent');
        $this->connection->executeQuery('DROP TABLE licence_authorization');
        $this->connection->executeQuery('DROP TABLE consent');
        $this->connection->executeQuery('DROP TABLE authorization');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE licence_consent (id INT AUTO_INCREMENT NOT NULL, licence_id INT DEFAULT NULL, consent_id VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, value TINYINT(1) NOT NULL, INDEX IDX_ADA5C2A326EF07C9 (licence_id), INDEX IDX_ADA5C2A341079D63 (consent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE licence_authorization (id INT AUTO_INCREMENT NOT NULL, licence_id INT DEFAULT NULL, authorization_id VARCHAR(25) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, value TINYINT(1) NOT NULL, INDEX IDX_5E02D72726EF07C9 (licence_id), INDEX IDX_5E02D7272F8B0EB2 (authorization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE consent (id VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, registration_form INT NOT NULL, category ENUM(\'school\', \'adult\', \'school_and_adult\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:LicenceCategory)\', membership ENUM(\'trial\', \'yearly\', \'trial_and_yearly\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:LicenceMembership)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE authorization (id VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, title VARCHAR(25) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, content LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, authorization_message VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, rejection_message VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, authorization_icon VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, rejection_icon VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, category ENUM(\'school\', \'adult\', \'school_and_adult\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:LicenceCategory)\', membership ENUM(\'trial\', \'yearly\', \'trial_and_yearly\') CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:LicenceMembership)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE licence_consent ADD CONSTRAINT FK_ADA5C2A341079D63 FOREIGN KEY (consent_id) REFERENCES consent (id)');
        $this->addSql('ALTER TABLE licence_consent ADD CONSTRAINT FK_ADA5C2A326EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_authorization ADD CONSTRAINT FK_5E02D72726EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_authorization ADD CONSTRAINT FK_5E02D7272F8B0EB2 FOREIGN KEY (authorization_id) REFERENCES authorization (id)');
        $this->addSql('ALTER TABLE licence_agreement DROP FOREIGN KEY FK_9A3C424626EF07C9');
        $this->addSql('ALTER TABLE licence_agreement DROP FOREIGN KEY FK_9A3C424624890B2B');
        $this->addSql('ALTER TABLE registration_step_agreement DROP FOREIGN KEY FK_D4C6CDBD33C34B3A');
        $this->addSql('ALTER TABLE registration_step_agreement DROP FOREIGN KEY FK_D4C6CDBD24890B2B');
        $this->addSql('DROP TABLE agreement');
        $this->addSql('DROP TABLE licence_agreement');
        $this->addSql('DROP TABLE registration_step_agreement');
    }

    private function getDisplayModes(): array
    {
        return [
            ['displayMode' => 'none', 'render' => 0],
            ['displayMode' => 'screen', 'render' => 1],
            ['displayMode' => 'file', 'render' => 2],
            ['displayMode' => 'screen_and_file', 'render' => 3],
            ['displayMode' => 'file_and_link', 'render' => 4],
        ];
    }
}
