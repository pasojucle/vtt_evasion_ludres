<?php

declare(strict_types=1);

namespace App\UseCase\v2\Session;

use App\Dto\Enum\ClusterResolverStatus;
use App\Dto\Service\OperationResult;
use App\Entity\BikeRide;
use App\Entity\Cluster;
use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\BikeTypeEnum;
use App\Entity\Enum\PracticeEnum;
use App\Entity\Level;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\Interface\ClusterRepositoryInterface;
use App\Repository\Interface\SessionRepositoryInterface;
use App\Service\Cluster\ClusterResolver;

class CreateSession
{
    public function __construct(
        private ClusterResolver $clusterResolver,
        private ClusterRepositoryInterface $clusterRepository,
        private SessionRepositoryInterface $sessionRepository,
    ){}

    public function __invoke(
        BikeRide $bikeRide,
        ?Cluster $cluster,
        User $user,
        ?PracticeEnum $practice,
        ?BikeTypeEnum $bikeType,
        AvailabilityEnum $availability,
    ): OperationResult
    {
        if (null === $cluster) {
            $result = ($this->clusterResolver)($bikeRide, $user);
            if ($result->status === ClusterResolverStatus::NO_CLUSTER_AVAILABLE) {
                return OperationResult::failure('Aucun groupe disponible pour cette activité.');
            }
            $cluster = ($result->status === ClusterResolverStatus::CAPACITY_EXCEEDED)
                ? $this->createNewUserLevelCluster($bikeRide, $result->cluster->getLevel())
                : $result->cluster;
        }

        $newSession = new Session();
        $newSession
            ->setUser($user)
            ->setCluster($cluster)
            ->setAvailability($availability)
            ->setBikeType($bikeType)
            ->setPractice($practice);

        $user->addSession($newSession);

        $this->sessionRepository->save($newSession);

        return OperationResult::success();
    }

    private function createNewUserLevelCluster(BikeRide $bikeRide, Level $level): Cluster
    {
        $newCluster = new Cluster();
        $count = $bikeRide->getClusters()
            ->filter(fn(Cluster $currentCluster) => $currentCluster->getLevel() === $level)
            ->count() + 1;
        $newCluster->setTitle($level->getTitle() . ' ' . $count)
            ->setLevel($level)
            ->setBikeRide($bikeRide)
            ->setMaxUsers(Cluster::SCHOOL_MAX_MEMBERS)
        ;
        $this->clusterRepository->save($newCluster, false);

        return $newCluster;
    }
}