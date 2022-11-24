<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\Cluster;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateClusters
{
    public function __construct(
        private LevelRepository $levelRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }
    
    public function execute($bikeRide)
    {
        if ($bikeRide->getBikeRideType()->isRegistrable()) {
            if ($bikeRide->getBikeRideType()->isSchool()) {
                $cluster = new Cluster();
                $cluster->setTitle(Cluster::CLUSTER_FRAME)
                    ->setRole('ROLE_FRAME')
                ;
                $bikeRide->addCluster($cluster);
                $this->entityManager->persist($cluster);
                $levels = $this->levelRepository->findAllTypeMember();
                if (null !== $levels) {
                    foreach ($levels as $level) {
                        $cluster = new Cluster();
                        $cluster->setTitle($level->getTitle())
                            ->setLevel($level)
                            ->setMaxUsers(Cluster::SCHOOL_MAX_MEMEBERS)
                        ;
                        $bikeRide->addCluster($cluster);
                        $this->entityManager->persist($cluster);
                    }
                }
            } else {
                $cluster = new Cluster();
                $cluster->setTitle('1er Groupe');
                $bikeRide->addCluster($cluster);
                $this->entityManager->persist($cluster);
            }
        }
    }
}
