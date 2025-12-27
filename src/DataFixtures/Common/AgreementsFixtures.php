<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Agreement;
use App\Entity\Enum\AgreementKindEnum;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceMembershipEnum;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class AgreementsFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const AGREEMENT_BACK_HOME_ALONE = 'agreement_back_home_alone';
    public const AGREEMENT_EMERGENCY_CARE_ADULT = 'agreement_emergency_care_adult';
    public const AGREEMENT_EMERGENCY_CARE_SCHOOL = 'agreement_emergency_care_school';
    public const AGREEMENT_HEALTH_ADULT = 'agreement_health_adult';
    public const AGREEMENT_HEALTH_ADULT_2 = 'agreement_health_adult_2';
    public const AGREEMENT_HEALTH_SCHOOL = 'agreement_health_school_2';
    public const AGREEMENT_HEALTH_SCHOOL_2 = 'agreement_image_use_adult';
    public const AGREEMENT_IMAGE_USE_ADULT = 'email_licence_validate'; 
    public const AGREEMENT_IMAGE_USE_SCHOOL = 'agreement_image_use_school';
    public const AGREEMENT_PARENTAL_CONSENT = 'agreement_parental_consent';
    public const AGREEMENT_RULES = 'agreement_rules';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $agreements = [
            self::AGREEMENT_BACK_HOME_ALONE => ['BACK_HOME_ALONE','Retour Domicile','<p>J’autorise mon enfant à rejoindre seul le domicile à l’issue de la séance. (- 12 ans exclu de rejoindre seul). Pour ce faire je m’engage à lui fournir un gilet jaune fluo ainsi qu’un dispositif d’éclairage avant (blanc) et arrière (rouge)</p>','school','trial_and_yearly','authorization','Autorisé à rentrer seul','Pas autorisé à rentrer seul','<i class="fa-solid fa-house"></i>','<i class="fa-solid fa-house"></i>','3','1'],
            self::AGREEMENT_EMERGENCY_CARE_ADULT => ['EMERGENCY_CARE_ADULT','Soins d\'urgence','J\’autorise les moniteurs fédéraux ainsi que les initiateurs fédéraux ou tout autre futurs moniteurs et/ou initiateurs, à prendre toute décision concernant les soins d’urgences qui s’avéreraient nécessaires et/ou obligatoires me concernant lors des activités organisées par le club. Je les autorise à transmettre aux services médicaux compétents les renseignements médicaux me concernant.','adult','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'4','1'],
            self::AGREEMENT_EMERGENCY_CARE_SCHOOL => ['EMERGENCY_CARE_SCHOOL','Soins d\'urgence','Je soussigné {{ prenom_nom_parent }} autorise les moniteurs fédéraux ainsi que les initiateurs fédéraux ou tout autre futurs moniteurs et/ou initiateurs, à prendre toute décision concernant les soins d’urgences qui s’avéreraient nécessaires et/ou obligatoires concernant mon enfant lors des activités organisées par le club. Je les autorise à transmettre aux services médicaux compétents les renseignements médicaux relatifs à mon enfant.','school','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'5','1'],
            self::AGREEMENT_HEALTH_ADULT => ['HEALTH_ADULT','Santé','J\'ai bien pris note de ces questions et comprends que certaines situations ou symptômes peuvent entraîner un risque pour ma santé et/ou pour mes performances.','adult','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'6','1'],
            self::AGREEMENT_HEALTH_ADULT_2 => ['HEALTH_ADULT_2','Santé','J\'atteste sur l\'honneur avoir déjà pris, ou prendre les dispositions nécessaires selon les recommandations données en cas de réponse positive à l\'une des questions des différents questionnaires.','adult','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'7','1'],
            self::AGREEMENT_HEALTH_SCHOOL => ['HEALTH_SCHOOL','Santé','Je fournis un certificat médical de moins de 6 mois (cyclotourisme) <b>OU</b> J\'atteste sur l\'honneur avoir renseigné le questionnaire de santé qui m\'a été remis par mon club.','school','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'8','1'],
            self::AGREEMENT_HEALTH_SCHOOL_2 => ['HEALTH_SCHOOL_2','Santé','J\'atteste sur l\'honneur avoir répondu par la négative à toutes les rubriques du questionnaire de santé et je reconnais expressément que les réponses apportées relèvent de ma responsabilité exclusive.','school','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'9','1'],
            self::AGREEMENT_IMAGE_USE_ADULT => ['IMAGE_USE_ADULT','Droit à l\'image','<p>Dans le cadre de nos activit&eacute;s, nous sommes amen&eacute;s &agrave; prendre des photos, des films ou des enregistrements sonores des membres du club pratiquant le VTT seul ou en groupe lors de nos sorties ou de diverses manifestations sportives. C&#39;est dans cet objectif que nous vous demandons l&rsquo;autorisation pour &ecirc;tre photographi&eacute;, film&eacute; ou enregistr&eacute;, et ce uniquement pour la communication de notre club. L&#39;association s&#39;interdit express&eacute;ment de proc&eacute;der &agrave; une exploitation des photographies, films et/ou interviews susceptibles de porter atteinte &agrave; la vie priv&eacute;e ou &agrave; la r&eacute;putation de ses adh&eacute;rents.</p><p>Je soussign&eacute;&nbsp; {{ prenom_nom }} {{ autorisation_droit_image }}&nbsp;le club VTT Evasion Ludres &agrave; utiliser ou faire utiliser ou reproduire ou faire reproduire mon image et ma voix. Je me reconnais enti&egrave;rement rempli de mes droits et je ne pourrai pr&eacute;tendre &agrave; aucune r&eacute;mun&eacute;ration pour l&#39;exploitation des droits vis&eacute;s &agrave; la pr&eacute;sente.</p>','adult','trial_and_yearly','authorization','Autorise le club à utiliser mon image','N\'autorise pas le club à utiliser mon image','<i class="fa-solid fa-camera"></i>','<i class="fa-solid fa-slash fa-camera"></i>','1','1'],
            self::AGREEMENT_IMAGE_USE_SCHOOL => ['IMAGE_USE_SCHOOL','Droit à l\'image','<p>Dans le cadre de nos activités, nous sommes amenés à prendre des photos, des films ou des enregistrements sonores des membres du club pratiquant le VTT seul ou en groupe lors de nos sorties ou de diverses manifestations sportives. C\'est dans cet objectif que nous vous demandons l\’autorisation pour être photographié, filmé ou enregistré, et ce uniquement pour la communication de notre club. L\'association s\'interdit expressément de procéder à une exploitation des photographies, films et/ou interviews susceptibles de porter atteinte à la vie privée ou à la réputation de ses adhérents. Je soussigné {{ prenom_nom_parent }} {{ autorisation_droit_image }} le club VTT Evasion Ludres à utiliser ou faire utiliser ou reproduire ou faire reproduire l\'image et la voix de mon enfant {{ prenom_nom_enfant }}. Je me  reconnais entièrement rempli de mes droits et je ne pourrai prétendre à aucune rémunération pour l\'exploitation des droits visés à la présente.<p>','school','trial_and_yearly','authorization','Autorise le club à utiliser mon image','N\'autorise pas le club à utiliser mon image','<i class="fa-solid fa-camera"></i>','<i class="fa-solid fa-slash fa-camera"></i>','2','1'],
            self::AGREEMENT_PARENTAL_CONSENT => ['PARENTAL_CONSENT','Autorisation parentale','Je soussigné {{ prenom_nom_parent }} Inscrit et autorise l\'enfant {{ prenom_nom_enfant }} à participer aux séances pédagogiques et à vélo de l’Ecole VTT Evasion Ludres.','school','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'0','1'],
            self::AGREEMENT_RULES => ['RULES','Réglement','Je m\'engage à respecter scrupuleusement le Code de la route, les statuts et règlements de la Fédération française de cyclotourisme, ainsi que les statuts et les règlements du VTT Evasion Ludres consultable sur le site www.vttevasionludres.fr','school_and_adult','trial_and_yearly','consent',NULL,NULL,NULL,NULL,'10','1']
        ];

        foreach ($agreements as $ref => [$id, $title,$content,$category,$membership,$kind,$authorizationMessage,$rejectionMessage,$authorizationIcon,$rejectionIcon,$orderBy,$enabled]) {
            $agreement = new Agreement();
            $agreement
                ->setId($id)
                ->setTitle($title)
                ->setContent($content)
                ->setCategory(LicenceCategoryEnum::from($category))
                ->setMembership(LicenceMembershipEnum::from($membership))
                ->setKind(AgreementKindEnum::from($kind))
                ->setAuthorizationMessage($authorizationMessage)
                ->setRejectionMessage($rejectionMessage)
                ->setAuthorizationIcon($authorizationIcon)
                ->setRejectionIcon($rejectionIcon)
                ->setOrderBy((int) $orderBy)
                ->setEnabled((bool) $enabled)
                ;

            $manager->persist($agreement);
            $this->addReference($ref, $agreement);
        }

        $manager->flush();
    }
}
