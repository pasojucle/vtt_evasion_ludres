<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Commune;
use App\Entity\Identity;
use App\Entity\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class IdentityFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const IDENTITY_ADMIN = 'identity_admin';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            UserAdminFixtures::class,
            CommuneFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $identity = new Identity();
        $identity->setUser($this->getReference(UserAdminFixtures::USER_ADMIN, User::class))
            ->setName('KOHLER')
            ->setFirstName('Sylvain')
            ->setBirthDate(new DateTime('1960-05-10'))
            ->setMobile('06 27 22 97 01')
            ->setProfession('Retraité')
            ->setEmail('scottscott184@yahoo.fr')
            ->setBirthCommune($this->getReference(CommuneFixtures::COMMUNE_NANCY, Commune::class))
            ->setEmergencyPhone('06 34 96 19 93')
            ->setEmergencyContact('Épouse');

        $manager->persist($identity);
        $this->addReference(self::IDENTITY_ADMIN, $identity);

        $manager->flush();
    }
}
