<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Address;
use App\Entity\Commune;
use App\Entity\Identity;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class IdentityFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const IDENTITY_ADMIN = 'identity_admin';
    public const IDENTITY_ADULT = 'identity_adult';
    public const IDENTITY_SCHOLL_MEMBER = 'identity_school_member';

    public const IDENTITIES = [
        self::IDENTITY_ADMIN => [
            UserFixtures::USER_ADMIN,
            AddressesFixtures::ADDRESS_ADMIN,
            'Cadre',
            'Carbone',
            'P60Y',
            '06 00 00 00 00', 
            'Retraité', 
            'cadre.carbon@test.fr',
            CommuneFixtures::COMMUNE_NANCY, 
            '06 00 00 00 01',
            'Épouse'
        ],
        self::IDENTITY_ADULT => [
            UserFixtures::USER_ADULT,
            AddressesFixtures::ADDRESS_ADULT,
            'Pédale',
            'Auto', 
            'P40Y', 
            '06 00 00 00 00', 
            'Imprimeur', 
            'pedale.auto@test.fr', 
            CommuneFixtures::COMMUNE_LUDRES,  
            '06 00 00 00 01', 
            'Épouse'
        ],
        self::IDENTITY_SCHOLL_MEMBER => [
            UserFixtures::USER_SCHOLL_MEMBER, 
            AddressesFixtures::ADDRESS_SCHOLL_MEMBER,
            'Pneu',
            'Tubeless', 
            'P10Y', 
            '06 00 00 00 00', 
            null, 
            'pneu.tubuless@test.fr',
            CommuneFixtures::COMMUNE_NANCY, 
            null,
            null
        ],
    ];

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CommuneFixtures::class,
            AddressesFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach(self::IDENTITIES as $ref => [$user, $address, $name, $firstName, $dateInterval, $mobile, $profession, $email, $birthCommune, $emergencyPhone, $emergencyContact]) {
            $identity = new Identity();
            $identity
                ->setUser($this->getReference($user, User::class))
                ->setAddress($this->getReference($address, Address::class))
                ->setName($name)
                ->setFirstName($firstName)
                ->setBirthDate((new DateTime())->sub(New DateInterval($dateInterval)))
                ->setMobile($mobile)
                ->setProfession($profession)
                ->setEmail($email)
                ->setBirthCommune($this->getReference($birthCommune, Commune::class))
                ->setEmergencyPhone($emergencyPhone)
                ->setEmergencyContact($emergencyContact);

            $manager->persist($identity);
            $this->addReference($ref, $identity);
        }
        
        $manager->flush();
    }
}
