<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\DataFixtures\Common\BikeRideTypeFixtures;
use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BikeRideFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const BIKE_RIDE_1 = 'bike_ride_1';
    public const BIKE_RIDE_2 = 'bike_ride_2';
    public const BIKE_RIDE_3 = 'bike_ride_3';
    public const BIKE_RIDE_4 = 'bike_ride_4';

    public const BIKE_RIDES = [
        self::BIKE_RIDE_1 => [BikeRideTypeFixtures::WINTER_MOUNTAIN_BIKING_SCHOOL],
        self::BIKE_RIDE_2 => [BikeRideTypeFixtures::WINTER_MOUNTAIN_BIKING_SCHOOL],
        self::BIKE_RIDE_3 => [BikeRideTypeFixtures::WINTER_MOUNTAIN_BIKING_SCHOOL],
        self::BIKE_RIDE_4 => [BikeRideTypeFixtures::WINTER_MOUNTAIN_BIKING_SCHOOL],
    ];

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            BikeRideTypeFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $startAt = (new DateTimeImmutable())->setTime(0, 0, 0);

        $i = 1;
        foreach (self::BIKE_RIDES as $ref => [$bikeRideTypeRef]) {
            $bikeRideStartAt = (clone $startAt)
                ->modify('next saturday')
                ->modify(sprintf('+%d weeks', $i - 1));
            $bikeRide = new BikeRide();
            $startAt = (new DateTimeImmutable())->setTime(0, 0, 0);
            $bikeRideType = $this->getReference($bikeRideTypeRef, BikeRideType::class);

            $bikeRide->setBikeRideType($bikeRideType)
                ->setStartAt($bikeRideStartAt)
                ->setTitle($bikeRideType->getName())
                ->setContent($bikeRideType->getContent())
                ->setDisplayDuration(8 * $i)
                ->setClosingDuration($bikeRideType->getClosingDuration() ?? 0);
                            
            $manager->persist($bikeRide);
            $this->addReference($ref, $bikeRide);
            ++$i;
        }

        $manager->flush();
    }
}
