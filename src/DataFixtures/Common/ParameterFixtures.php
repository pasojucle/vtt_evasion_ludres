<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Parameter;
use App\Entity\ParameterGroup;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class ParameterFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const PARAMETER_MAINTENANCE_MODE = 'parameter_maintenance_mode';
    public const SPARAMETER_CHOOL_TESTING_REGISTRATION = 'sparameter_chool_testing_registration';
    public const PARAMETER_ERROR_USER_AGENT_IGNORE = 'sparameter_chool_testing_registration';
    public const PARAMETER_ERROR_URL_IGNORE = 'parameter_error_url_ignore';
    public const PARAMETER_TEST_MODE = 'parameter_test_mode';
    public const PARAMETER_SEASON_START_AT = 'parameter_season_start_at';
    public const PARAMETER_COVERAGE_FORM_AVAILABLE_AT = 'parameter_coverage_form_available_at';
    public const PARAMETER_REQUIREMENT_SEASON_LICENCE_AT = 'parameter_requirement_season_licence_at'; 
    public const PARAMETER_SECOND_HAND_DURATION = 'parameter_second_hand_duration';
    public const PARAMETER_NEW_SEASON_RE_REGISTRATION_ENABLED = 'parameter_new_season_re_registration_enabled';
    public const PARAMETER_DEDUPLICATION_MAILER_ENABLED = 'parameter_deduplication_mailer_enabled';
    public const PARAMETER_SLIDESHOW_MAX_DISK_SIZE = 'parameter_slideshow_max_disk_size';
    public const PARAMETER_LOG_DURATION = 'parameter_log_duration';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            ParameterGroupFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $parameters = [
            self::PARAMETER_MAINTENANCE_MODE => ["MAINTENANCE_MODE","Mode maintenance","3","0",ParameterGroupFixtures::PARAMETER_GROUP_MAINTENANCE],
            self::SPARAMETER_CHOOL_TESTING_REGISTRATION => ["SCHOOL_TESTING_REGISTRATION","Autoriser l'inscription aux 3 séances d'essai pour l'école vtt","3","1",ParameterGroupFixtures::PARAMETER_GROUP_REGISTRATION],
            self::PARAMETER_ERROR_USER_AGENT_IGNORE => ["ERROR_USER_AGENT_IGNORE","Liste des user-agents à exclure du log des erreurs","4","{'0':'Googlebot','1':'AdsBot-Google','2':'Googlebot-Image','3':'bingbot','4':'bot','5':'ltx71','6':'GoogleImageProxy','7':'SiteLockSpider','9':'Barkrowler','108':'Go-http-client'}",ParameterGroupFixtures::PARAMETER_GROUP_TOOLS],
            self::PARAMETER_ERROR_URL_IGNORE => ["ERROR_URL_IGNORE","Liste des url à exclure du log des erreurs","4","{'0':'wlwmanifest','1':'xmlrpc.php','2':'wp-content','3':'wp-admin','4':'.env','5':'yahoo','7':'well-known','8':'.html','9':'wp-login','106':'binance','129':'wp-json','122':'google'}",ParameterGroupFixtures::PARAMETER_GROUP_TOOLS],
            self::PARAMETER_TEST_MODE => ["TEST_MODE","Mode de test","3","0",ParameterGroupFixtures::PARAMETER_GROUP_MAINTENANCE],
            self::PARAMETER_SEASON_START_AT => ["SEASON_START_AT","Date du début de la nouvelle saison","5","{'day':'01','month':'09'}",ParameterGroupFixtures::PARAMETER_GROUP_REGISTRATION],
            self::PARAMETER_COVERAGE_FORM_AVAILABLE_AT => ["COVERAGE_FORM_AVAILABLE_AT","Date du début de la disponibilité du bulletin d'assurance","5","{'day':'1','month':'11'}",ParameterGroupFixtures::PARAMETER_GROUP_REGISTRATION],
            self::PARAMETER_REQUIREMENT_SEASON_LICENCE_AT => ["REQUIREMENT_SEASON_LICENCE_AT","Date à laquelle la licence {{ saison_actuelle }} est requise pour s'incrire à une sortie","5","{'day':'1','month':'1'}",ParameterGroupFixtures::PARAMETER_GROUP_REGISTRATION],
            self::PARAMETER_SECOND_HAND_DURATION => ["SECOND_HAND_DURATION","Durré d'affichage d'une annonce d'occasion (en jours)","2","30",ParameterGroupFixtures::PARAMETER_GROUP_SECOND_HAND],
            self::PARAMETER_NEW_SEASON_RE_REGISTRATION_ENABLED => ["NEW_SEASON_RE_REGISTRATION_ENABLED","Autoriser les ré-inscriptions pour {{ saison_actuelle }}","3","1",ParameterGroupFixtures::PARAMETER_GROUP_REGISTRATION],
            self::PARAMETER_DEDUPLICATION_MAILER_ENABLED => ["DEDUPLICATION_MAILER_ENABLED","Activer l'envoi d'une copie des mails","3","0",ParameterGroupFixtures::PARAMETER_GROUP_MAINTENANCE],
            self::PARAMETER_SLIDESHOW_MAX_DISK_SIZE => ["SLIDESHOW_MAX_DISK_SIZE","Espace disque alloué pour le diaporama","6","20G",ParameterGroupFixtures::PARAMETER_GROUP_SLIDESHOW],
            self::PARAMETER_LOG_DURATION => ["LOG_DURATION","Durrée de conservation des logs (en jours)","2","90",ParameterGroupFixtures::PARAMETER_GROUP_MAINTENANCE]
        ];

        foreach ($parameters as $ref => [$name, $label, $type, $value, $parameterGroup]) {

            $parameterGroupRef = $this->getReference($parameterGroup, ParameterGroup::class);
            $parameter = new Parameter();
            $parameter->setName($name)
                ->setLabel($label)
                ->setType((int) $type)
                ->setValue($value)
                ->setType((int) $type)
                ->setParameterGroup($parameterGroupRef)
                ;

            $manager->persist($parameter);
            $this->addReference($ref, $parameter);
        }

        $manager->flush();
    }
}
