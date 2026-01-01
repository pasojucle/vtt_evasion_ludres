<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\BikeRideType;
use App\Entity\Enum\RegistrationEnum;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class BikeRideTypeFixtures extends AbstractFixture implements FixtureGroupInterface
{
    public const DEPT_54 = 'department_54';

    public const OCCASIONAL_OUTING = 'occasional_outing';
    public const ADULT_HIKING = 'adult_hiking';
    public const SCHOOL_HOLIDAYS = 'school_holidays';
    public const FEDERAL_ACTIVITY = 'federal_activity';
    public const CLUB_ACTIVITIES = 'club_activities';
    public const CLUB_MEETING = 'club_meeting';
    public const SUMMER_MOUNTAIN_BIKING_SCHOOL = 'summer_mountain_biking_school';
    public const WINTER_MOUNTAIN_BIKING_SCHOOL = 'winter_mountain_biking_school';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $bikeRideTypes = [
            self::OCCASIONAL_OUTING => ['Sortie occasionnelle',NULL,'0','0','1','{"0":"Groupe 1"}','0','2','0','cluster'],
            self::ADULT_HIKING =>['Rando adultes et ados (sans encadrement)','<p>Rendez-vous &agrave; <strong>9h00</strong> au club sur le plateau de Ludres</p>','0','1','0','{"0":"Groupe 1 (avions de chasse),","1":"Groupe 2 (randonneurs avertis)","2":"Groupe 3 (debutants. remise en forme)."}','0','0','1','cluster'],
            self::SUMMER_MOUNTAIN_BIKING_SCHOOL => ['École VTT 🌞','<p>École VTT sur le plateau de Ludres.</p><p><strong>HORAIRES D\'ÉTÉ :&nbsp; 🌞</strong></p><p><strong>de 13H45 précise à 17h00 ( il faut être présent à 13h45 )</strong></p><p><strong>Goûter à la fin de la séance.</strong></p>','1','0','1','[]','1','2','0','school'],
            self::WINTER_MOUNTAIN_BIKING_SCHOOL => ['École VTT.','<p>École VTT sur le plateau de Ludres.</p><p><strong>HORAIRES D\'HIVER : ❆</strong><span style="background-color:rgb(255,255,255);color:rgb(54,53,49);">⛄❄</span></p><p><strong>de 13H45 précise à 16h30 ( il faut être présent à 13h45 )</strong></p><p><strong>Goûter à la fin de la séance.</strong></p>','1','0','1','[]','1','2','0','school'],
            self::SCHOOL_HOLIDAYS => ['École VTT: Vacances scolaires','<p><strong>Il n&#39;y aura pas de s&eacute;ances d&#39;&eacute;cole VTT ce samedi</strong></p>','0','0','1','[]','0',NULL,'0','none'],
            self::FEDERAL_ACTIVITY => ['Activité fédérale',NULL,'1','0','1','{"0":"Groupe 1"}','1','2','0','cluster'],
            self::CLUB_ACTIVITIES => ['Animation club',NULL,'0','0','1','{"0":"Groupe 1"}','0','2','0','cluster'],
            self::CLUB_MEETING => ['Réunion du club',NULL,'0','0','0','["Participants"]','0','2','0','cluster'],
        ];

        foreach($bikeRideTypes as $ref => [$name, $content, $isCompensable, $showMemberList, $useLevels, $clusters, $needFramers, $closingDuration, $displayBikeKind, $registration]) {
            $bikeRideType = new BikeRideType();
            $bikeRideType->setName($name)
                ->setContent($content)
                ->setIsCompensable((bool) $isCompensable)
                ->setShowMemberList((bool) $showMemberList)
                ->setuseLevels((bool) $useLevels)
                ->setClusters(json_decode($clusters, true))
                ->setNeedFramers((bool) $needFramers)
                ->setClosingDuration((int) $closingDuration)
                ->setDisplayBikeKind((bool) $displayBikeKind)
                ->setRegistration(RegistrationEnum::from($registration));
                
                $manager->persist($bikeRideType);
                $this->addReference($ref, $bikeRideType);
        }

        $manager->flush();
    }
}
