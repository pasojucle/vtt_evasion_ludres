<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\Entity\Enum\LicenceCategoryEnum;
use App\Entity\Enum\LicenceStateEnum;
use App\Entity\Licence;
use App\Entity\User;
use App\Service\SeasonService;
use DateTime;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LicenceFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const LICENCE_ADMIN = 'licence_admin';
    public const LICENCE_ADULT = 'licence_adult';
    public const LICENCE_SCHOLL_MEMBER = 'licence_school_member';
    
    public const LICENCES = [
        self::LICENCE_ADMIN => [UserFixtures::USER_ADMIN, 3, LicenceCategoryEnum::ADULT, LicenceStateEnum::YEARLY_FILE_REGISTRED],
        self::LICENCE_ADULT => [UserFixtures::USER_ADULT, 2, LicenceCategoryEnum::ADULT, LicenceStateEnum::YEARLY_FILE_REGISTRED],
        self::LICENCE_SCHOLL_MEMBER => [UserFixtures::USER_SCHOLL_MEMBER, 2, LicenceCategoryEnum::SCHOOL, LicenceStateEnum::YEARLY_FILE_REGISTRED],
    ];
    

    public function __construct(
        private SeasonService $seasonService,
    ) {
    }

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ParameterFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach(self::LICENCES  as $ref => [$user, $coverage, $category, $state]) {
            $currentSeason = $this->seasonService->getCurrentSeason();
            $licence = new Licence();
            $licence->setUser($this->getReference($user, User::class))
                ->setCoverage($coverage)
                ->setCategory($category)
                ->setSeason($currentSeason)
                ->setCreatedAt(new DateTime(sprintf('%s-09-01', $currentSeason)))
                ->setState($state);

            $manager->persist($licence);
            $this->addReference($ref, $licence);
        }

        $manager->flush();
    }
}
