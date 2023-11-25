<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Parameter;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231106195220 extends AbstractMigration
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
                'name' => 'EMAIL_REGISTRATION',
                'newName' => 'EMAIL_ACCOUNT_CREATED',
                'value' => '<p>Vous venez de vous inscrire au club pour 3 s&eacute;ances d&#39;essai gratuites depuis notre site.</p><p>Ci-joint, votre dossier d&#39;inscription.</p><p>Imprimer le fichier &quot;Docs_&agrave;_redonner_au_club.pdf&quot; et signer le <strong>coupon assurance</strong> et le <strong>bulletin d&#39;inscription</strong>. Transmettre les documents au club.</p><p>Concerver le document &quot;Informations.pdf&quot;.</p><p><strong>Voici votre indentifiant pour vous connecter : </strong>{{ numero_licence }}<br />Vous seul(e) connaissez le mot de passe</p><p><strong>Pour valider votre inscription, vous devez obligatoirement&nbsp;</strong>vous CONNECTER sur notre site ET VOUS INSCRIRE&nbsp;&agrave; une sortie ou &agrave; une s&eacute;ance de l&#39;&eacute;cole VTT</p> <p><br /> <a href="https://vttevasionludres.fr/member/programme" style="padding: 12px 20px;border: none;background-color: #f89c37;border-radius: unset;line-height: initial;box-sizing: border-box;color: #ffffff; text-decoration: none;">Voir le programme</a></p>',
            ],
            [
                'name' => 'EMAIL_LICENCE_VALIDATE',
                'newName' => 'EMAIL_LICENCE_VALIDATE',
                'value' => '<p>Veuillez trouver votre num&eacute;ro de licence qui sera d&eacute;sormais votre identifiant pour vous connecter &agrave; votre compte.</p><p><strong>{{ numero_licence }} </strong></p><p>Vous seul.e connaissez le mot de passe qui reste inchang&eacute;</p>',
            ],
            [
                'name' => 'EMAIL_REGISTRATION_ERROR',
                'newName' => 'EMAIL_REGISTRATION_ERROR',
                'value' => '<p>Faisant suite aux probl&egrave;mes que vous avez rencontr&eacute; lors de votre inscription, nous vous invitons &agrave; suivre <a href="http://vttevasionludres.fr/mon-compte/inscription/1">ce lien</a>&nbsp;pour acc&eacute;der &agrave; votre dossier.</p><p>Apr&egrave;s avoir compl&eacute;t&eacute; toutes les informations manquantes, vous pourrez t&eacute;l&eacute;charger le dossier &agrave; nous remettre sign&eacute;.</p><p><strong>Voici votre identifiant pour vous connecter : </strong>{{ numero_licence }}</p><p>Vous seul(e) connaissez le mot de passe</p>',
            ],
        ];
        foreach($parameters as $parameter) {
           $this->addSql('UPDATE `parameter` SET `name`=:newName,`value`=  :value WHERE `name`= :name', $parameter); 
        }
        

        $parameter = [
            'name' => 'EMAIL_REGISTRATION',
            'label' => 'Message mail lors de l\'inscription',
            'type' => Parameter::TYPE_TEXT,
            'value' => '<p>Vous venez de vous inscrire au club pour la saisson {{ saison_actuelle }} depuis notre site.</p><p>Ci-joint, votre dossier d&#39;inscription.</p><p>Imprimer le fichier "Docs_à_redonner_au_club.pdf" et signer le <b>coupon assurance</b> et le <b>bulletin d&#39;inscription</b>. Transmettre les documents au club avec votre paiement et certificat médical si besoin (OBLIGATOIRE POUR TOUTE NOUVELLE INSCRIPTION).</p><p>Concerver le document "Informations.pdf".</p>',
            'group' => $this->connection->executeQuery('SELECT `id` FROM `parameter_group` WHERE `name` =\'MESSAGES\'')->fetchOne(),
        ];

        $this->addSql('INSERT INTO `parameter`(`name`, `label`, `type`, `value`, `parameter_group_id`) VALUES (:name, :label, :type, :value, :group)', $parameter);
    
        $registrationsSteps = $this->connection->executeQuery('SELECT id FROM `registration_step`')->fetchAllAssociative();
        $registrationsStepsIds = array_column($registrationsSteps, 'id');

        $registrationSteps = [
            [9, 'Téléchargement final', NULL, 7, 0, '<p style="text-align:center"><strong>VOTRE DOSSIER D&#39;INSCRIPTION VOUS A &Eacute;T&Eacute; ENVOY&Eacute; PAR MAIL &Agrave; L&#39;ADRESSE<span style="font-size:18px"> </span>{{ email_principal }}.</strong></p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><span style="font-family:Arial,Helvetica,sans-serif">Imprimez&nbsp;le fichier <strong>&quot;Docs_&agrave;_redonner_au_club.pdf&quot;</strong>&nbsp;<br />
            et&nbsp;signer le <strong>coupon assurance</strong> et <strong>bulletin d&#39;inscription</strong>.</span></p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><span style="font-family:Arial,Helvetica,sans-serif">Transmettre les documents au club avec votre paiement et certificat m&eacute;dical si besoin<br />
            <strong>(OBLIGATOIRE POUR TOUTE NOUVELLE INSCRIPTION).</strong></span></p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><strong>TOUT DOSSIER INCOMPLET NE SERA PAS ACCEPT&Eacute; ET L&#39;INSCRIPTION NE SERA PAS VALID&Eacute;E</strong></p>', NULL, 0, 9, 1, 0],
            [20, 'Téléchargement essai', NULL, 7, 1, '<p style="text-align:center">Votre dossier d&#39;inscription vous a &eacute;t&eacute; envoy&eacute; par mail &agrave; l&#39;adresse {{ email_principal }}.</p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><span style="font-family:Arial,Helvetica,sans-serif">Imprimez&nbsp;le fichier &quot;Docs_&agrave;_redonner_au_club.pdf&quot;&nbsp;et&nbsp;signer le <strong>coupon assurance</strong> et <strong>bulletin d&#39;inscription</strong>.</span></p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><span style="font-family:Arial,Helvetica,sans-serif">Transmettre les documents au club.</span></p><p style="text-align:center">&nbsp;</p><p style="text-align:center">TOUT DOSSIER INCOMPLET NE SERA PAS ACCEPT&Eacute; ET L&#39;INSCRIPTION NE SERA PAS VALID&Eacute;E</p>', NULL, 1, 9, 0, 0],
        ];
            
            $keys = ['id', 'title', 'filename', 'form', 'orderBy', 'content', 'category', 'testingRender', 'group', 'finalRender', 'personal'];
            
            $this->addSql('DELETE FROM `registration_step` WHERE `id`=1');

            foreach($registrationSteps as $step) {
                $registrationStep = [];
                foreach($step as $key => $value) {
                    $registrationStep[$keys[$key]] = $value;
                }
                if (in_array($registrationStep['id'], $registrationsStepsIds)) {
                    $this->addSql('UPDATE `registration_step` SET `title`=:title,`filename`=:filename,`form`=:form,`order_by`=:orderBy,`content`=:content,`category`=:category,`testing_render`=:testingRender,`registration_step_group_id`=:group,`final_render`=:finalRender,`personal`=:personal WHERE `id`=:id', $registrationStep);
                } else {
                    $this->addSql('INSERT INTO `registration_step` (`id`, `title`, `filename`, `form`, `order_by`, `content`, `category`, `testing_render`, `registration_step_group_id`, `final_render`, `personal`) VALUES (:id, :title, :filename, :form, :orderBy, :content, :category, :testingRender, :group,:finalRender, :personal)', $registrationStep);
                }
            };
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
