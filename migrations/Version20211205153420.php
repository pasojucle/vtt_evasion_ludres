<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Form\UserType;
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
            ['id' => 1, 'title' => 'Dossier d\'inscription',],
            ['id' => 2, 'title' => 'Informations',],
            ['id' => 3, 'title' => 'Tableaux des garanties',],
            ['id' => 4, 'title' => 'Tarifs',],
            ['id' => 5, 'title' => 'Assurance',],
            ['id' => 6, 'title' => 'Autorisations',],
            ['id' => 7, 'title' => 'Santé',],
            ['id' => 8, 'title' => 'Validation',],
            ['id' => 9, 'title' => 'Téléchargement',],
        ];
        foreach($registrationStepGroups as $registrationStepGroup) {
            $registrationStepGroup['orderBy'] = $registrationStepGroup['id'] - 1;
            $this->addSql('INSERT INTO `registration_step_group`(`id`, `title`, `order_by`) VALUES (:id, :title, :orderBy)', $registrationStepGroup);
        }
        
        $registrationSteps = [
            [
                'registrationStepGroup' => 2,
                'title' => 'Informations de l\'adhérent',
                'testingRender' => 3,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 2,
                'title' => 'Informations du parent ou tuteur de l\'enfant',
                'testingRender' => 3,
                'finalRender' => 3,
                'orderBy' => 2,
            ],
            [
                'registrationStepGroup' => 3,
                'title' => 'Tableaux des garanties',
                'testingRender' => 0,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 4,
                'title' => 'Tarifs',
                'testingRender' => 3,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 5,
                'title' => 'Assurance',
                'testingRender' => 2,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 6,
                'title' => 'Droit image',
                'newTitle' => 'Autorisations adulte',
                'testingRender' => 3,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 6,
                'title' => 'Autorisations',
                'newTitle' => 'Autorisations mineur',
                'testingRender' => 3,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 7,
                'title' => 'Questionnaire de santé adulte',
                'testingRender' => 0,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 7,
                'title' => 'Questionnaire de santé mineur',
                'testingRender' => 0,
                'finalRender' => 3,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 7,
                'title' => 'Santé de l\'enfant',
                'testingRender' => 0,
                'finalRender' => 3,
                'orderBy' => 2,
            ],
            [
                'registrationStepGroup' => 8,
                'title' => 'Validation',
                'testingRender' => 1,
                'finalRender' => 1,
                'orderBy' => 1,
            ],
            [
                'registrationStepGroup' => 9,
                'title' => 'Téléchargement',
                'testingRender' => 1,
                'finalRender' => 1,
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

        $testingContent = '<p>{{ entete }}</p><table border="0" cellpadding="1" cellspacing="1" class="table-fixed" style="width:100%">	<tbody>		<tr>			<td><img alt="" src="/images/logo-pdf.jpg" style="height:110px; width:110px" /></td>			<td colspan="3" rowspan="1">			<h1 style="text-align:center">{{ titre_licence }} - {{ saison }}</h1>			</td>			<td><img alt="" src="/images/ffvelo-logo-square-75x75.png" style="float:right; height:50px; width:50px" /></td>		</tr>		<tr>			<td style="height:0px">&nbsp;</td>			<td style="height:0px">&nbsp;</td>			<td style="height:0px">&nbsp;</td>			<td style="height:0px">&nbsp;</td>			<td style="height:0px">&nbsp;</td>		</tr>	</tbody></table><p>{{ entete }}</p><p>{{ pied_page }}</p><h2>Assurance</h2><table class="table-fixed" style="width:100%">	<tbody>		<tr>			<td><strong>Type de licence : </strong>{{ type_licence }}</td>			<td><strong>Type d&#39;assurance : </strong>{{ type_assurance}}</td>		</tr>	</tbody></table><p>&nbsp;</p><table class="table-fixed" style="width:100%">	<tbody>		<tr>			<td>{{ cotisation }}</td>		</tr>		<tr>			<td>{{ necessite_sertificat_medical }}</td>		</tr>	</tbody></table><p>&nbsp;</p><p>J&#39;accepte le r&egrave;glement de la F&eacute;d&eacute;ration Fran&ccedil;aise de Cyclotourisme ainsi que celui du VTT Evasion Ludres consultable sur le site www.vttevasionludres.fr</p><p>&nbsp;</p><p>IL EST IMPERATIF DE SIGNER L&#39;INFORMATION D&#39;ASSURANCE GRATUITE JOINTE A CE DOCUMENT</p><p>&nbsp;</p><table class="table-fixed" style="width:100%">	<tbody>		<tr>			<td>Fait &agrave; Ludres le {{ date }}</td>			<td>Signature : (pr&eacute;c&eacute;d&eacute;e de la mention &lsquo;&rsquo;Lu et approuv&eacute;&rsquo;&rsquo;)</td>		</tr>	</tbody></table><p>{{ pied_page }}</p>';

        $finalContent = '<p>{{ entete }}</p><table border="0" cellpadding="1" cellspacing="1" class="table-fixed" style="width:100%">	<tbody>		<tr>			<td><img alt="" src="/images/logo-pdf.jpg" style="height:110px; width:110px" /></td>			<td colspan="3" rowspan="1">			<h1 style="text-align:center">{{ titre_licence }} - {{ saison }}</h1>			</td>			<td><img alt="" src="/images/ffvelo-logo-square-75x75.png" style="float:right; height:50px; width:50px" /></td>		</tr>		<tr>			<td style="height:1px">&nbsp;</td>			<td style="height:1px">&nbsp;</td>			<td style="height:1px">&nbsp;</td>			<td style="height:1px">&nbsp;</td>			<td style="height:1px">&nbsp;</td>		</tr>	</tbody></table><p>{{ entete }}</p><p>{{ pied_page }}</p><h2>Assurance</h2><table class="table-fixed" style="width:100%">	<tbody>		<tr>			<td><strong>Type de licence : </strong>{{ type_licence }}</td>			<td><strong>Type d&#39;assurance : </strong>{{ type_assurance}}</td>		</tr>	</tbody></table><p>&nbsp;</p><table class="table-fixed" style="width:100%">	<tbody>		<tr>			<td>{{ cotisation }}</td>		</tr>		<tr>			<td>{{ necessite_sertificat_medical }}</td>		</tr>	</tbody></table><p>&nbsp;</p><p>J&#39;atteste avoir r&eacute;pondu &quot;NON&quot; &agrave; toutes les questions du questionnaire de sant&eacute; et ne pas fournir de nouveau certificat m&eacute;dical pour ma r&eacute;inscription (sauf pour la 1&egrave;re demande de licence ou date de validit&eacute;).</p><p>Dans le cas contraire, je fournis un certificat de non contre-indication &agrave; la pratique du VTT, dat&eacute; de -12 mois.</p><p>&nbsp;</p><table class="table-fixed" style="width:100%">	<tbody>		<tr>			<td>Fait &agrave; Ludres le {{ date }}</td>			<td>Signature : (pr&eacute;c&eacute;d&eacute;e de la mention &lsquo;&rsquo;Lu et approuv&eacute;&rsquo;&rsquo;)</td>		</tr>	</tbody></table><p>&nbsp;</p><p>{{ pied_page }}</p>';
        $registrationSteps = [
            [
               'registrationStepGroup' => 1,
                'title' => 'Dossier d\'inscription essai',
                'content' => $testingContent,
                'form' => UserType::FORM_REGISTRATION_DOCUMENT,
                'testingRender' => 2,
                'finalRender' => 0,
                'orderBy' => 1, 
            ],
            [
                'registrationStepGroup' => 1,
                 'title' => 'Dossier d\'inscription final',
                 'content' => $finalContent,
                 'form' => UserType::FORM_REGISTRATION_DOCUMENT,
                 'testingRender' => 0,
                 'finalRender' => 2,
                 'orderBy' => 1, 
             ],
            
        ];
        foreach ($registrationSteps as $registrationStep){
            $this->addSql('INSERT INTO `registration_step`(`registration_step_group_id`, `title`, `form`,`content`,`testing_render`, `final_render`,`order_by`) 
            VALUES (:registrationStepGroup, :title, :form, :content, :testingRender, :finalRender, :orderBy)', $registrationStep);
        }
        $this->addSql("INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES ('SCHOOL_TESTING_REGISTRATION_MESSAGE', 'Message à afficher en cas de cloture des inscriptions',1,'L\'inscription à l\'école vtt est close pour la saison {{ saison }}', 3)");
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
