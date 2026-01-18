<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Level;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class UserFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const USER_ADMIN = 'user_admin';
    public const USER_ADULT = 'user_adult';
    public const USER_SCHOLL_MEMBER = 'user_school_member';

    public const USERS = [
        self::USER_ADMIN => ['ADMIN', '$2y$13$41w5VsgQjTE1bqziUjvtiOYdW2rXIiAqe.9X/zDPobcJXWTWjUem.', ["ROLE_ADMIN"], true, LevelFixtures::LEVEL_ADULT, false, true, false],
        self::USER_ADULT => ['ADULT', '$2y$13$41w5VsgQjTE1bqziUjvtiOYdW2rXIiAqe.9X/zDPobcJXWTWjUem.', ["ROLE_USER"], true, LevelFixtures::LEVEL_ADULT, false, true, false],
        self::USER_SCHOLL_MEMBER => ['SCHHOL_MEMBER', '$2y$13$41w5VsgQjTE1bqziUjvtiOYdW2rXIiAqe.9X/zDPobcJXWTWjUem.', ["ROLE_USER"], true, LevelFixtures::LEVEL_CHAMOIS, false, true, false],
    ];

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
        foreach (self::USERS as $ref => [$licenceNumber, $password, $roles, $active, $level, $passWordMustChanged, $loginSend, $protected]) {
            $admin = new User();
            $admin->setLicenceNumber($licenceNumber)
                ->setPassword($password)
                ->setRoles($roles)
                ->setActive($active)
                ->setLevel($this->getReference($level, Level::class))
                ->setPasswordMustBeChanged($passWordMustChanged)
                ->setLoginSend($loginSend)
                ->setProtected($protected);

            $manager->persist($admin);
            $this->addReference($ref, $admin);
        }

        $manager->flush();
    }
    
    public static function getLicenceNumberFromReference(string $reference): string
    {
        if (array_key_exists($reference, self::USERS)) {
            return self::USERS[$reference][0];
        }

        throw new Exception("Référence inconnue");
    }
}
