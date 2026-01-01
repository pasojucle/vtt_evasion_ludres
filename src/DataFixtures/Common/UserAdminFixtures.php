<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\User;
use App\Entity\Level;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserAdminFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const USER_ADMIN = 'user_admin';

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            LevelFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setLicenceNumber('624758')
            ->setPassword('$2y$13$41w5VsgQjTE1bqziUjvtiOYdW2rXIiAqe.9X/zDPobcJXWTWjUem.')
            ->setRoles(["ROLE_ADMIN"])
            ->setActive(true)
            ->setLevel($this->getReference(LevelFixtures::LEVEL_ADULT, Level::class))
            ->setPasswordMustBeChanged(false)
            ->setLoginSend(true)
            ->setProtected(false);

        $manager->persist($admin);
        $this->addReference(self::USER_ADMIN, $admin);

        $manager->flush();
    }
}
