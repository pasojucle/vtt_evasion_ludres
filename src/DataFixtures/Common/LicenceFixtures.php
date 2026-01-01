<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use DateTime;
use App\Entity\User;
use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Service\SeasonService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class LicenceFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const LICENCE_ADMIN = 'licence_admin';

    public function __construct(
        private SeasonService $seasonService,
    )
    {
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            UserAdminFixtures::class,
            ParameterFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $lastSeason = $this->seasonService->getCurrentSeason() - 1;
        $licence = new Licence();
        $licence->setUser($this->getReference(UserAdminFixtures::USER_ADMIN, User::class))
            ->setCoverage(3)
            ->setCategory(LicenceCategoryEnum::ADULT)
            ->setSeason($lastSeason)
            ->setCreatedAt(new DateTime(sprintf('%s-09-01', $lastSeason)))
            ->setState(LicenceStateEnum::YEARLY_FILE_REGISTRED);

        $manager->persist($licence);
        $this->addReference(self::LICENCE_ADMIN, $licence);

        $manager->flush();
    }
}
