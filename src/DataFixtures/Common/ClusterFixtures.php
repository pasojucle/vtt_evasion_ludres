<?php

declare(strict_types=1);

namespace App\DataFixtures\Common;

use App\DataFixtures\Common\BikeRideFixtures;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Level;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ClusterFixtures extends AbstractFixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public const CLUSTER_1 = 'cluster_1';
    public const CLUSTER_2 = 'cluster_2';
    public const CLUSTER_3 = 'cluster_3';
    public const CLUSTER_4 = 'cluster_4';

    public const CLUSTERS = [
        self::CLUSTER_1 => [BikeRideFixtures::BIKE_RIDE_1],
        self::CLUSTER_2 => [BikeRideFixtures::BIKE_RIDE_2],
        self::CLUSTER_3 => [BikeRideFixtures::BIKE_RIDE_3],
        self::CLUSTER_4 => [BikeRideFixtures::BIKE_RIDE_4],
    ];

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function getDependencies(): array
    {
        return [
            BikeRideFixtures::class,
            LevelFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::CLUSTERS as $ref => [$bikeRide]) {
            foreach (LevelFixtures::SCHOOL_LEVELS as $levelRef) {
                $level = $this->getReference($levelRef, Level::class);
                $cluster = new Cluster();
                $cluster->setBikeRide($this->getReference($bikeRide, BikeRide::class))
                    ->setTitle($level->getTitle())
                    ->setLevel($level);
                
                $manager->persist($cluster);
                // $this->addReference($ref, $cluster);
            }
        }

        $manager->flush();
    }
}
