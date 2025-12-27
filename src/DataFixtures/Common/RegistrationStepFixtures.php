<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\RegistrationStep;
use App\Entity\Enum\DisplayModeEnum;
use App\Entity\RegistrationStepGroup;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\RegistrationFormEnum;
use App\DataFixtures\Common\MembershipFeeFixtures;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RegistrationStepFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const REGIRSTRATION_STEP_2 = 'registration_step_2';
    public const REGIRSTRATION_STEP_3 = 'registration_step_3';
    public const REGIRSTRATION_STEP_4 = 'registration_step_4';
    public const REGIRSTRATION_STEP_5 = 'registration_step_5';
    public const REGIRSTRATION_STEP_7 = 'registration_step_7';
    public const REGIRSTRATION_STEP_8 = 'registration_step_8';
    public const REGIRSTRATION_STEP_9 = 'registration_step_9';
    public const REGIRSTRATION_STEP_10 = 'registration_step_10';
    public const REGIRSTRATION_STEP_11 = 'registration_step_11';
    public const REGIRSTRATION_STEP_12 = 'registration_step_12';
    public const REGIRSTRATION_STEP_13 = 'registration_step_13';
    public const REGIRSTRATION_STEP_14 = 'registration_step_14';
    public const REGIRSTRATION_STEP_15 = 'registration_step_15';
    public const REGIRSTRATION_STEP_16 = 'registration_step_16';
    public const REGIRSTRATION_STEP_17 = 'registration_step_17';
    public const REGIRSTRATION_STEP_18 = 'registration_step_18';
    public const REGIRSTRATION_STEP_19 = 'registration_step_19';
    public const REGIRSTRATION_STEP_20 = 'registration_step_20';

    public static function getGroups(): array
    {
        return ['test'];
    }


    public function getDependencies(): array
    {
        return [
            MembershipFeeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $registrationSteps = [
            self::REGIRSTRATION_STEP_2 => ['Tarifs',NULL,'1','<p style="text-align:justify"><span style="font-family:Arial,Helvetica,sans-serif">Ch&egrave;ques vacances, Coupons sport (ANCV), Pass&#39;Sport, Pass&#39;Jeunes accept&eacute;s</span></p><p style="text-align:justify"><span style="font-size:12px"><strong><u><span style="color:#e74c3c">CE MONTANT COMPREND POUR 1 ANNEE : VOTRE LICENCE + ASSURANCE + COTISATION CLUB </span></u></strong></span></p><p style="text-align:justify"><span style="font-family:Arial,Helvetica,sans-serif"><strong>IMPORTANT:</strong><br />Voir d&eacute;tails des garanties assurances f&eacute;d&eacute;rales..<br />ATTENTION&nbsp; :<br />POUR DECLARER VOS SINISTRES, vous devez vous connecter sur votre espace adh&eacute;rent sur le site de la f&eacute;d&eacute;ration : <strong><span style="color:#c0392b">ffct.org</span></strong> et en informer votre club. Conservez toujours vos factures d&#39;achats d&#39;&eacute;quipements. L&#39;option Grand Braquet n&#39;est prise en consid&eacute;ration qu&#39;apr&egrave;s inscription et seulement &agrave; partir du 1er&nbsp;janvier de l&#39;ann&eacute;e suivante. Pour les inscriptions en cours d&#39;ann&eacute;e jusqu&#39;&agrave; fin d&eacute;cembre, le licenci&eacute; est assur&eacute;&nbsp;uniquement en petit braquet.<br />VOTRE LICENCE est d&eacute;mat&eacute;rialis&eacute;e. Vous la recevrez par mail d&egrave;s que votre adh&eacute;sion sera effective sur le site f&eacute;d&eacute;ral. Elle comporte <span style="color:#c0392b"><strong>vos identifiants pour vous connecter sur le site f&eacute;d&eacute;ral (ffct.org) </strong></span>notamment en cas de&nbsp;d&eacute;claration de sinistre.</span></p>',RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_MEMBERSHIP_FEE,'1','school_and_adult','screen_and_file','screen_and_file','membership_fee'],
            self::REGIRSTRATION_STEP_3 => ['Questionnaire de santé ffvélo',NULL,'0',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_HEATH,'1','school_and_adult','screen','screen','health_question'],
            self::REGIRSTRATION_STEP_4 => ['Informations du parent ou tuteur de l\'enfant',NULL,'2',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_INFO,'0','school','screen_and_file','screen_and_file','gardians'],
            self::REGIRSTRATION_STEP_5 => ['Informations de l\'adhérent',NULL,'1',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_INFO,'0','school_and_adult','screen_and_file','screen_and_file','member'],
            self::REGIRSTRATION_STEP_7 => ['Assurance','NOTICE-D-INFORMATION-DU-LICENCIE-2025-A-SIGNER-converti-673cd39d1dfa3.pdf','1',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_COVERAGE,'0','school_and_adult','none','screen','licence_coverage'],
            self::REGIRSTRATION_STEP_8 => ['Autorisations adulte',NULL,'1',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_AUTHORIZATION,'0','adult','screen_and_file','screen_and_file','licence_agreements'],
            self::REGIRSTRATION_STEP_9 => ['Téléchargement final',NULL,'0','<p><strong>VOTRE DOSSIER D\'INSCRIPTION VOUS A ÉTÉ ENVOYÉ PAR MAIL À L\'ADRESSE {{ email_principal }}.</strong></p><p>&nbsp;</p><p>Imprimez&nbsp;le fichier <strong>"Docs_à_redonner_au_club.pdf"</strong>&nbsp;et&nbsp;signer le <strong>coupon assurance</strong> et <strong>bulletin d\'inscription</strong>.</p><p>&nbsp;</p><p>Transmettre les documents au club avec votre paiement.</p><p>&nbsp;</p><p><strong>TOUT DOSSIER INCOMPLET NE SERA PAS ACCEPTÉ ET L\'INSCRIPTION NE SERA PAS VALIDÉE</strong></p>',RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_DOWNLOAD,'0','school_and_adult','none','screen','registration_file'],
            self::REGIRSTRATION_STEP_10 => ['Autorisations mineur',NULL,'1',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_AUTHORIZATION,'0','school','screen_and_file','screen_and_file','licence_agreements'],
            self::REGIRSTRATION_STEP_11 => ['Validation',NULL,'1','<p style="text-align:center">Voici le r&eacute;capitulatif des informations saisies.</p><p style="text-align:center">Nous vous invitons &agrave; les relire et &agrave; les modifier si besoin.</p><p style="text-align:center">Apr&egrave;s validation, aucune modification ne sera possible.</p><p style="text-align:center">NE PAS OUBLIER DE REDONNER AU CLUB VOTRE BULLETIN D&#39;INSCRIPTION ET LE COUPON ASSURANCE,</p><p style="text-align:center"><strong>LES</strong> <strong>DEUX&nbsp;DOIVENT ETRE SIGNES</strong></p>',RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_VALIDATE,'0','school_and_adult','screen','screen','overview'],
            self::REGIRSTRATION_STEP_12 => ['Questionnaire de santé ffvélo [pdf)','Questionnaire-sante-2023-6562578acb498.pdf','1',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_HEATH,'1','school_and_adult','file','file','health_question'],
            self::REGIRSTRATION_STEP_13 => ['Dossier d\'inscription essai',NULL,'1','<p>{{ entete }}</p><p>&nbsp;</p><figure class="table" style="width:100%;"><table><tbody><tr><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/logo-75-pdf.jpg" width="75" height="75"></figure></td><td colspan="3" rowspan="1"><h1 style="text-align:center">{{ titre_licence }} - {{ saison }}</h1></td><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/ffvelo-logo-square-75x75.png" width="75" height="75"></figure></td></tr><tr><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td><td style="height:0px;">&nbsp;</td></tr></tbody></table></figure><p>{{ entete }}</p><p>{{ pied_page }}</p><p>&nbsp;</p><p>IL EST IMPERATIF DE SIGNER L\'INFORMATION D\'ASSURANCE GRATUITE JOINTE A CE DOCUMENT</p><p>&nbsp;</p><figure class="table" style="width:100%;"><table><tbody><tr><td>Fait à Ludres le {{ date }}</td><td>Signature : (précédée de la mention ‘’Lu et approuvé’’)</td></tr></tbody></table></figure><p>{{ pied_page }}</p>',RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_REGISTRATION,'0','school_and_adult','file','none','registration_document'],
            self::REGIRSTRATION_STEP_14 => ['Dossier d\'inscription final',NULL,'1','<p>{{ entete }}</p><figure class="table" style="width:100%;"><table><tbody><tr><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/logo-75-pdf.jpg" width="75" height="75"></figure></td><td colspan="3" rowspan="1"><h1 style="text-align:center">{{ titre_licence }} - {{ saison }}</h1></td><td><figure class="image"><img style="aspect-ratio:75/75;" src="/logos/ffvelo-logo-square-75x75.png" width="75" height="75"></figure></td></tr><tr><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td><td style="height:1px;">&nbsp;</td></tr></tbody></table></figure><p>{{ entete }}</p><p>{{ pied_page }}</p><p>&nbsp;</p><p>&nbsp;</p><figure class="table" style="width:100%;"><table><tbody><tr><td>Fait à Ludres le {{ date }}</td><td>Signature : (précédée de la mention ‘’Lu et approuvé’’)</td></tr></tbody></table></figure><p>&nbsp;</p><p>{{ pied_page }}</p>',RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_REGISTRATION,'0','school_and_adult','none','file','registration_document'],
            self::REGIRSTRATION_STEP_15 => ['Reconnaître les symptômes cardiaques','reconnaitre-les-symptomes-cardiaques-656257a98359e.pdf','2',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_HEATH,'1','school_and_adult','file_and_link','file_and_link','health_question'],
            self::REGIRSTRATION_STEP_16 => ['Facteurs de risques et pathologies cardiaques','facteurs-de-risques-et-pathologies-cardiaques-656257c4787a6.pdf','3',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_HEATH,'1','school_and_adult','file_and_link','file_and_link','health_question'],
            self::REGIRSTRATION_STEP_17 => ['Les problématiques liées au sport','les-problematiques-liees-au-sport-656257e22c0e3.pdf','4',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_HEATH,'1','school_and_adult','file_and_link','file_and_link','health_question'],
            self::REGIRSTRATION_STEP_18 => ['Assurance (pages 1 et 2)','NOTICE-D-INFORMATION-DU-LICENCIE-2025-A-SIGNER-1-2-673cd0a469189.pdf','2',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_COVERAGE,'1','school_and_adult','file','file','licence_coverage'],
            self::REGIRSTRATION_STEP_19 => ['Assurance (pages 3)','NOTICE-D-INFORMATION-DU-LICENCIE-2025-A-SIGNER-3-673cd0ff1361e.pdf','3',NULL,RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_COVERAGE,'0','school_and_adult','file','file','licence_coverage'],
            self::REGIRSTRATION_STEP_20 => ['Téléchargement essai',NULL,'1','<p style="text-align:center">Votre dossier d&#39;inscription vous a &eacute;t&eacute; envoy&eacute; par mail &agrave; l&#39;adresse {{ email_principal }}.</p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><span style="font-family:Arial,Helvetica,sans-serif">Imprimez&nbsp;le fichier &quot;Docs_&agrave;_redonner_au_club.pdf&quot;&nbsp;et&nbsp;signer le <strong>coupon assurance</strong> et <strong>bulletin d&#39;inscription</strong>.</span></p><p style="text-align:center">&nbsp;</p><p style="text-align:center"><span style="font-family:Arial,Helvetica,sans-serif">Transmettre les documents au club.</span></p><p style="text-align:center">&nbsp;</p><p style="text-align:center">TOUT DOSSIER INCOMPLET NE SERA PAS ACCEPT&Eacute; ET L&#39;INSCRIPTION NE SERA PAS VALID&Eacute;E</p>',RegistrationStepGroupFixtures::REGIRSTRATION_STEP_GROUP_DOWNLOAD,'0','school_and_adult','screen','none','registration_file'],
        ];

        foreach ($registrationSteps as $ref => [$title, $filename, $orderBy, $content, $registrationStepGroup, $personal, $category, $trialDisplayMode, $yearlyDisplayMode, $form]) {
            $registrationStepGroupRef= $this->getReference($registrationStepGroup, RegistrationStepGroup::class);

            $registrationStep = new RegistrationStep();
            $registrationStep->setTitle($title)
                ->setFilename($filename)
                ->setOrderBy((int) $orderBy)
                ->setContent($content)
                ->setRegistrationStepGroup($registrationStepGroupRef)
                ->setPersonal((bool) $personal)
                ->setCategory(LicenceCategoryEnum::from($category))
                ->setTrialDisplayMode(DisplayModeEnum::from($trialDisplayMode))
                ->setYearlyDisplayMode(DisplayModeEnum::from($yearlyDisplayMode))
                ->setForm(RegistrationFormEnum::from($form));

            $manager->persist($registrationStep);
            $this->addReference($ref, $registrationStep);
        }

        $manager->flush();
    }
}
