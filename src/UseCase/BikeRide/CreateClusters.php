<?php

declare(strict_types=1);

namespace App\UseCase\BikeRide;

use App\Entity\BikeRide;
use App\Entity\BikeRideType;
use App\Entity\Cluster;
use App\Entity\Enum\RegistrationEnum;
use App\Repository\LevelRepository;
use Doctrine\ORM\EntityManagerInterface;

class CreateClusters
{
    private int $position = 0;
    public function __construct(
        private LevelRepository $levelRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }
    
    public function execute($bikeRide)
    {
        if ($bikeRide->getBikeRideType()->isNeedFramers()) {
            $this->addFramer($bikeRide);
        }
        if (RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
            $this->addSchoolClusters($bikeRide);
        }
        if (RegistrationEnum::CLUSTERS === $bikeRide->getBikeRideType()->getRegistration()) {
            $this->addClusters($bikeRide);
        }
    }

    private function addSchoolClusters(BikeRide $bikeRide): void
    {
        $levels = $this->levelRepository->findAllTypeMember();
        foreach ($levels as $level) {
            $cluster = new Cluster();
            $cluster->setTitle($level->getTitle())
                ->setLevel($level)
                ->setMaxUsers(Cluster::SCHOOL_MAX_MEMBERS)
                ->setPosition($this->position)
            ;
            $bikeRide->addCluster($cluster);
            $this->entityManager->persist($cluster);
            ++$this->position;
        }
    }

    private function addClusters(BikeRide $bikeRide): void
    {
        foreach ($bikeRide->getBikeRideType()->getClusters() as $clusterTitle) {
            $cluster = new Cluster();
            $cluster->setTitle($clusterTitle)
                ->setPosition($this->position)
            ;
            $bikeRide->addCluster($cluster);
            $this->entityManager->persist($cluster);
            ++$this->position;
        }
    }

    private function addFramer(BikeRide $bikeRide): void
    {
        $cluster = new Cluster();
        $cluster->setTitle(Cluster::CLUSTER_FRAME)
            ->setRole('ROLE_FRAME')
            ->setPosition($this->position)
        ;
        $bikeRide->addCluster($cluster);
        $this->entityManager->persist($cluster);
        ++$this->position;
    }
}
