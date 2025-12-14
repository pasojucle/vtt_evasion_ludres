<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Form\UserType;
use App\Entity\Licence;
use App\Entity\RegistrationStep;
use Doctrine\DBAL\Schema\Schema;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use App\Entity\Enum\RegistrationFormEnum;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251112184232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE authorization (id VARCHAR(25) NOT NULL, title VARCHAR(25) NOT NULL, content LONGTEXT NOT NULL, authorization_message VARCHAR(50) NOT NULL, rejection_message VARCHAR(50) NOT NULL, authorization_icon VARCHAR(50) NOT NULL, rejection_icon VARCHAR(50) NOT NULL, category ENUM(\'school\', \'adult\', \'school_and_adult\') NOT NULL COMMENT \'(DC2Type:LicenceCategory)\', membership ENUM(\'trial\', \'yearly\', \'trial_and_yearly\') NOT NULL COMMENT \'(DC2Type:LicenceMembership)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consent (id VARCHAR(25) NOT NULL, title VARCHAR(50) NOT NULL, content LONGTEXT NOT NULL, registration_form INT NOT NULL, category ENUM(\'school\', \'adult\', \'school_and_adult\') NOT NULL COMMENT \'(DC2Type:LicenceCategory)\', membership ENUM(\'trial\', \'yearly\', \'trial_and_yearly\') NOT NULL COMMENT \'(DC2Type:LicenceMembership)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licence_authorization (id INT AUTO_INCREMENT NOT NULL, licence_id INT DEFAULT NULL, authorization_id VARCHAR(25) DEFAULT NULL, value TINYINT(1) NOT NULL, INDEX IDX_5E02D72726EF07C9 (licence_id), INDEX IDX_5E02D7272F8B0EB2 (authorization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE licence_consent (id INT AUTO_INCREMENT NOT NULL, licence_id INT DEFAULT NULL, consent_id VARCHAR(25) DEFAULT NULL, value TINYINT(1) NOT NULL, INDEX IDX_ADA5C2A326EF07C9 (licence_id), INDEX IDX_ADA5C2A341079D63 (consent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE licence_authorization ADD CONSTRAINT FK_5E02D72726EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_authorization ADD CONSTRAINT FK_5E02D7272F8B0EB2 FOREIGN KEY (authorization_id) REFERENCES authorization (id)');
        $this->addSql('ALTER TABLE licence_consent ADD CONSTRAINT FK_ADA5C2A326EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_consent ADD CONSTRAINT FK_ADA5C2A341079D63 FOREIGN KEY (consent_id) REFERENCES consent (id)');
        $this->addSql('ALTER TABLE licence CHANGE category category ENUM(\'school\', \'adult\', \'school_and_adult\') NOT NULL COMMENT \'(DC2Type:LicenceCategory)\'');
        $this->addSql('ALTER TABLE registration_step ADD category_tmp ENUM(\'school\', \'adult\', \'school_and_adult\') DEFAULT \'school_and_adult\' NOT NULL COMMENT \'(DC2Type:LicenceCategory)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE licence CHANGE category category INT NOT NULL');
        $this->addSql('ALTER TABLE registration_step CHANGE category category INT DEFAULT NULL');
       	$this->addSql('CREATE TABLE sworn_certification (id INT AUTO_INCREMENT NOT NULL, label LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, school TINYINT(1) DEFAULT 0 NOT NULL, adult TINYINT(1) DEFAULT 0 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE licence_sworn_certification (id INT AUTO_INCREMENT NOT NULL, licence_id INT NOT NULL, sworn_certification_id INT NOT NULL, value TINYINT(1) NOT NULL, INDEX IDX_25B43C0D26EF07C9 (licence_id), INDEX IDX_25B43C0D313364D9 (sworn_certification_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE licence_sworn_certification ADD CONSTRAINT FK_25B43C0D26EF07C9 FOREIGN KEY (licence_id) REFERENCES licence (id)');
        $this->addSql('ALTER TABLE licence_sworn_certification ADD CONSTRAINT FK_25B43C0D313364D9 FOREIGN KEY (sworn_certification_id) REFERENCES sworn_certification (id)');
        $this->addSql('ALTER TABLE licence_authorization DROP FOREIGN KEY FK_5E02D72726EF07C9');
        $this->addSql('ALTER TABLE licence_authorization DROP FOREIGN KEY FK_5E02D7272F8B0EB2');
        $this->addSql('ALTER TABLE licence_consent DROP FOREIGN KEY FK_ADA5C2A326EF07C9');
        $this->addSql('ALTER TABLE licence_consent DROP FOREIGN KEY FK_ADA5C2A341079D63');
        $this->addSql('DROP TABLE authorization');
        $this->addSql('DROP TABLE consent');
        $this->addSql('DROP TABLE licence_authorization');
        $this->addSql('DROP TABLE licence_consent');
    }
    public function postUp(Schema $schema): void
    {
        $this->connection->executeQuery('UPDATE licence SET category=:category WHERE category=1', ['category' => LicenceCategoryEnum::SCHOOL->value]);
        $this->connection->executeQuery('UPDATE licence SET category=:category WHERE category=2', ['category' => LicenceCategoryEnum::ADULT->value]);        
        $this->connection->executeQuery('UPDATE registration_step SET category_tmp=:category WHERE category=1', ['category' => LicenceCategoryEnum::SCHOOL->value]);
        $this->connection->executeQuery('UPDATE registration_step SET category_tmp=:category WHERE category=2', ['category' => LicenceCategoryEnum::ADULT->value]);
        $this->connection->executeQuery('ALTER TABLE registration_step DROP category');
        $this->connection->executeQuery('ALTER TABLE registration_step CHANGE category_tmp category ENUM(\'school\', \'adult\', \'school_and_adult\') DEFAULT \'school_and_adult\' NOT NULL COMMENT \'(DC2Type:LicenceCategory)\'');

        $authorizations = [
            [
                'id' => 'BACK_HOME_ALONE',
                'title' => 'Retour Domicile',
                'content' => '<p>J’autorise mon enfant à rejoindre seul le domicile à l’issue de la séance. (- 12 ans exclu de rejoindre seul). Pour ce faire je m’engage à lui fournir un gilet jaune fluo ainsi qu’un dispositif d’éclairage avant (blanc) et arrière (rouge)</p>',
                'category' => LicenceCategoryEnum::SCHOOL->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'authorizationMessage' => 'Autorisé à rentrer seul',
                'rejectionMessage' => 'Pas autorisé à rentrer seul',
                'authorizationIcon' => '<i class="fa-solid fa-house"></i>',
                'rejectionIcon' => '<i class="fa-solid fa-house"></i>',
            ],
            [
                'id' => 'IMAGE_USE_SCHOOL',
                'title' => 'Droit à l\'image',
                'content' => '<p>Dans le cadre de nos activités, nous sommes amenés à prendre des photos, des films ou des enregistrements sonores des membres du club pratiquant le VTT seul ou en groupe lors de nos sorties ou de diverses manifestations sportives. C\'est dans cet objectif que nous vous demandons l’autorisation pour être photographié, filmé ou enregistré, et ce uniquement pour la communication de notre club. L\'association s\'interdit expressément de procéder à une exploitation des photographies, films et/ou interviews susceptibles de porter atteinte à la vie privée ou à la réputation de ses adhérents. Je soussigné {{ prenom_nom_parent }} {{ autorisation_droit_image }} le club VTT Evasion Ludres à utiliser ou faire utiliser ou reproduire ou faire reproduire l\'image et la voix de mon enfant {{ prenom_nom_enfant }}. Je me  reconnais entièrement rempli de mes droits et je ne pourrai prétendre à aucune rémunération pour l\'exploitation des droits visés à la présente.<p>',
                'category' => LicenceCategoryEnum::SCHOOL->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'authorizationMessage' => 'Autorise le club à utiliser mon image',
                'rejectionMessage' => 'N\'autorise pas le club à utiliser mon image',
                'authorizationIcon' => '<i class="fa-solid fa-camera"></i>',
                'rejectionIcon' => '<i class="fa-solid fa-slash fa-camera"></i>',
            ],
            [
                'id' => 'IMAGE_USE_ADULT',
                'title' => 'Droit à l\'image',
                'content' => '<p>Dans le cadre de nos activit&eacute;s, nous sommes amen&eacute;s &agrave; prendre des photos, des films ou des enregistrements sonores des membres du club pratiquant le VTT seul ou en groupe lors de nos sorties ou de diverses manifestations sportives. C&#39;est dans cet objectif que nous vous demandons l&rsquo;autorisation pour &ecirc;tre photographi&eacute;, film&eacute; ou enregistr&eacute;, et ce uniquement pour la communication de notre club. L&#39;association s&#39;interdit express&eacute;ment de proc&eacute;der &agrave; une exploitation des photographies, films et/ou interviews susceptibles de porter atteinte &agrave; la vie priv&eacute;e ou &agrave; la r&eacute;putation de ses adh&eacute;rents.</p><p>Je soussign&eacute;&nbsp; {{ prenom_nom }} {{ autorisation_droit_image }}&nbsp;le club VTT Evasion Ludres &agrave; utiliser ou faire utiliser ou reproduire ou faire reproduire mon image et ma voix. Je me reconnais enti&egrave;rement rempli de mes droits et je ne pourrai pr&eacute;tendre &agrave; aucune r&eacute;mun&eacute;ration pour l&#39;exploitation des droits vis&eacute;s &agrave; la pr&eacute;sente.</p>',
                'category' => LicenceCategoryEnum::ADULT->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'authorizationMessage' => 'Autorise le club à utiliser mon image',
                'rejectionMessage' => 'N\'autorise pas le club à utiliser mon image',
                'authorizationIcon' => '<i class="fa-solid fa-camera"></i>',
                'rejectionIcon' => '<i class="fa-solid fa-slash fa-camera"></i>',
            ],
        ];

        foreach($authorizations as $authorization) {
            $this->connection->executeQuery('INSERT INTO `authorization`(`id`, `title`, `content`, `authorization_message`, `rejection_message`, `authorization_icon`, `rejection_icon`, `category`, `membership`) VALUES (:id, :title, :content, :authorizationMessage, :rejectionMessage, :authorizationIcon, :rejectionIcon, :category, :membership)', $authorization);
        }

        $licences = $this->connection->executeQuery('SELECT * FROM licence')->fetchAllAssociative();
        $licencesByUser = [];
        foreach($licences as $licence) {
            $licencesByUser[$licence['user_id']][] = $licence;
        }

        $userApprovals = $this->connection->executeQuery('SELECT * FROM approval')->fetchAllAssociative();
        foreach($userApprovals as $userApproval) {
            if (array_key_exists($userApproval['user_id'], $licencesByUser)) {
                foreach($licencesByUser[$userApproval['user_id']] as $licence) {
                    $authorization = [
                        'licence_id' => $licence['id'],
                        'authorization' => $this->getAuthorization($userApproval, $licence),
                        'value' => $userApproval['value'] ?? 0,
                    ];
                    $this->connection->executeQuery('INSERT INTO `licence_authorization`(`licence_id`, `authorization_id`, `value`) VALUES (:licence_id, :authorization, :value)', $authorization);
                }
            }
        }
        
        $consents = [
            [
                'id' => 'HEALTH_ADULT',
                'title' => 'Santé',
                'content' => 'J\'ai bien pris note de ces questions et comprends que certaines situations ou symptômes peuvent entraîner un risque pour ma santé et/ou pour mes performances.',
                'category' => LicenceCategoryEnum::ADULT->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 1,
                'swornCertificationId' => 1,
            ], 
            [
                'id' => 'HEALTH_ADULT_2',
                'title' => 'Santé',
                'content' => 'J\'atteste sur l\'honneur avoir déjà pris, ou prendre les dispositions nécessaires selon les recommandations données en cas de réponse positive à l\'une des questions des différents questionnaires.',
                'category' => LicenceCategoryEnum::ADULT->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 1,
                'swornCertificationId' => 2,
            ],
            [
                'id' => 'HEALTH_SCHOOL',
                'title' => 'Santé',
                'content' => 'Je fournis un certificat médical de moins de 6 mois (cyclotourisme) <b>OU</b> J\'atteste sur l\'honneur avoir renseigné le questionnaire de santé qui m\'a été remis par mon club.',
                'category' => LicenceCategoryEnum::SCHOOL->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 1,
                'swornCertificationId' => 3,
            ],
            [
                'id' => 'HEALTH_SCHOOL_2',
                'title' => 'Santé',
                'content' => 'J\'atteste sur l\'honneur avoir répondu par la négative à toutes les rubriques du questionnaire de santé et je reconnais expressément que les réponses apportées relèvent de ma responsabilité exclusive.',
                'category' => LicenceCategoryEnum::SCHOOL->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 1,
                'swornCertificationId' => 4,
            ], 
            [
                'id' => 'RULES',
                'title' => 'Réglement',
                'content' => 'Je m\'engage à respecter scrupuleusement le Code de la route, les statuts et règlements de la Fédération française de cyclotourisme, ainsi que les statuts et les règlements du VTT Evasion Ludres consultable sur le site www.vttevasionludres.fr',
                'category' => LicenceCategoryEnum::SCHOOL_AND_ADULT->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 9,
                'swornCertificationId' => 5,
            ],
            [
                'id' => 'PARENTAL_CONSENT',
                'title' => 'Autorisation parentale',
                'content' => 'Je soussigné {{ prenom_nom_parent }} Inscrit et autorise l\'enfant {{ prenom_nom_enfant }} à participer aux séances pédagogiques et à vélo de l’Ecole VTT Evasion Ludres.',
                'category' => LicenceCategoryEnum::SCHOOL->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 4,
            ],
            [
                'id' => 'EMERGENCY_CARE_SCHOOL',
                'title' => 'Soins d\'urgence',
                'content' => 'Je soussigné {{ prenom_nom_parent }} autorise les moniteurs fédéraux ainsi que les initiateurs fédéraux ou tout autre futurs moniteurs et/ou initiateurs, à prendre toute décision concernant les soins d’urgences qui s’avéreraient nécessaires et/ou obligatoires concernant mon enfant lors des activités organisées par le club. Je les autorise à transmettre aux services médicaux compétents les renseignements médicaux relatifs à mon enfant.',
                'category' => LicenceCategoryEnum::SCHOOL->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 4,
            ],
            [
                'id' => 'EMERGENCY_CARE_ADULT',
                'title' => 'Soins d\'urgence',
                'content' => 'J’autorise les moniteurs fédéraux ainsi que les initiateurs fédéraux ou tout autre futurs moniteurs et/ou initiateurs, à prendre toute décision concernant les soins d’urgences qui s’avéreraient nécessaires et/ou obligatoires me concernant lors des activités organisées par le club. Je les autorise à transmettre aux services médicaux compétents les renseignements médicaux me concernant.',
                'category' => LicenceCategoryEnum::ADULT->value,
                'membership' => LicenceMembershipEnum::TRIAL_AND_YEARLY->value,
                'registrationForm' => 4,
            ],
        ];
        foreach($consents as $consent) {
            $this->connection->executeQuery('INSERT INTO `consent`(`id`, `title`, `content`, `category`, `membership`, `registration_form`) VALUES (:id, :title, :content, :category, :membership, :registrationForm)', $consent);
        }

        $swornCertificationToConsents = [];
        foreach ($consents as $consent) {
            if (array_key_exists('swornCertificationId', $consent)) {
                $swornCertificationToConsents[$consent['swornCertificationId']] = $consent['id'];
            }
        }
        $licencesSwornCerticications = $this->connection->executeQuery('SELECT * FROM licence_sworn_certification')->fetchAllAssociative();
        foreach($licencesSwornCerticications as $licencesSwornCerticication) {
            $licencesSwornCerticication['consent'] = $swornCertificationToConsents[$licencesSwornCerticication['sworn_certification_id']];
            $this->connection->executeQuery('INSERT INTO `licence_consent`(`licence_id`, `consent_id`, `value`) VALUES (:licence_id, :consent, :value)', $licencesSwornCerticication);
        }
        foreach($licences as $licence) {
            $consentIds = (LicenceCategoryEnum::SCHOOL->value === $licence['category'])
                ? ['PARENTAL_CONSENT', 'EMERGENCY_CARE_SCHOOL']
                : ['EMERGENCY_CARE_ADULT'];
            foreach ($consentIds as $consentId) {
                $licenceConsent = [
                    'licenceId' => $licence['id'],
                    'consentId' => $consentId,
                    'value' => true,
                ];
                $this->connection->executeQuery('INSERT INTO `licence_consent`(`licence_id`, `consent_id`, `value`) VALUES (:licenceId, :consentId, :value)', $licenceConsent);
            }
        }

        $this->connection->executeQuery('ALTER TABLE licence_sworn_certification DROP FOREIGN KEY FK_25B43C0D26EF07C9');
        $this->connection->executeQuery('ALTER TABLE licence_sworn_certification DROP FOREIGN KEY FK_25B43C0D313364D9');
        $this->connection->executeQuery('ALTER TABLE approval DROP FOREIGN KEY FK_16E0952BA76ED395');
        $this->connection->executeQuery('DROP TABLE sworn_certification');
        $this->connection->executeQuery('DROP TABLE licence_sworn_certification');
        $this->connection->executeQuery('DROP TABLE approval');

        $registrationSteps = [
            [
                'id' => 13,
                'title' => 'Dossier d\'inscription essai',
                'content' => '<p>{{ entete }}</p><p>&nbsp;</p><figure class="table" style="width:100%;"><table><tbody><tr><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/logo-75-pdf.jpg" width="75" height="75"></figure></td><td colspan="3" rowspan="1"><h1 style="text-align:center">{{ titre_licence }} - {{ saison }}</h1></td><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/ffvelo-logo-square-75x75.png" width="75" height="75"></figure></td></tr><tr><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td></tr></tbody></table></figure><p>{{ entete }}</p><p>{{ pied_page }}</p><p>&nbsp;</p><p>IL EST IMPERATIF DE SIGNER L\'INFORMATION D\'ASSURANCE GRATUITE JOINTE A CE DOCUMENT</p><p>&nbsp;</p><figure class="table" style="width:100%;"><table><tbody><tr><td>Fait à Ludres le {{ date }}</td><td>Signature : (précédée de la mention ‘’Lu et approuvé’’)</td></tr></tbody></table></figure><p>{{ pied_page }}</p>'
            ],
            [
                'id' => 14,
                'title' => 'Dossier d\'inscription final',
                'content' => '<p>{{ entete }}</p><figure class="table" style="width:100%;"><table><tbody><tr><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/logo-75-pdf.jpg" width="75" height="75"></figure></td><td colspan="3" rowspan="1"><h1 style="text-align:center">{{ titre_licence }} - {{ saison }}</h1></td><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/ffvelo-logo-square-75x75.png" width="75" height="75"></figure></td></tr><tr><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td></tr></tbody></table></figure><p>{{ entete }}</p><p>{{ pied_page }}</p><p>&nbsp;</p><p>&nbsp;</p><figure class="table" style="width:100%;"><table><tbody><tr><td>Fait à Ludres le {{ date }}</td><td>Signature : (précédée de la mention ‘’Lu et approuvé’’)</td></tr></tbody></table></figure><p>&nbsp;</p><p>{{ pied_page }}</p>'
            ],
            [
                'id' => 8,
                'title' => 'Autorisations adulte',
                'content' => null,
            ],
            [
                'id' => 10,
                'title' => 'Autorisations mineur',
                'content' => null,
            ],
            [
                'id' => 7,
                'title' => 'Assurance',
                'content' => null,
            ],
        ];
        foreach($registrationSteps as $registrationStep) {
            $this->connection->executeQuery('UPDATE `registration_step` SET `content`= :content WHERE `id`=:id', $registrationStep);
        }

        $registrationSteps = [
            [
                'id' => 15,
                'title' => 'Reconnaître les symptômes cardiaques',
                'testingRender' => 4,
            ],
            [
                'id' => 16,
                'title' => 'Facteurs de risques et pathologies cardiaques',
                'testingRender' => 4,
            ],
            [
                'id' => 17,
                'title' => 'Les problématiques liées au sport',
                'testingRender' => 4,
            ],
        ];
        foreach($registrationSteps as $registrationStep) {
            $this->connection->executeQuery('UPDATE `registration_step` SET `testing_render`= :testingRender WHERE `id`=:id', $registrationStep);
        }
    }

    private function getAuthorization(array $userApproval, array $licence): string
    {
        if (1 === $userApproval['type']) {
            return (Licence::CATEGORY_ADULT === $licence['category'])
                ? 'IMAGE_USE_ADULT'
                : 'IMAGE_USE_SCHOOL';
                
        }

        return 'BACK_HOME_ALONE';
    }
}
