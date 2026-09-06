<?php

declare(strict_types=1);

namespace App\Service\Cluster;

use App\Dto\Service\ClusterResolverResult;
use App\Dto\Enum\ClusterResolverStatus;
use App\Entity\BikeRide;
use App\Entity\Enum\LevelType;
use App\Entity\Enum\RegistrationEnum;
use App\Entity\Session;
use App\Entity\User;

class ClusterResolver
{
    public function __invoke(BikeRide $bikeRide, User $user): ?ClusterResolverResult
    {
        $userCluster = null;

        if ($bikeRide->getBikeRideType()->isNeedFramers() && LevelType::SCHOOL !== $user->getLevel()->getType()) {
            foreach ($bikeRide->getClusters() as $cluster) {
                if ('ROLE_FRAME' === $cluster->getRole()) {
                    return ClusterResolverResult::success($cluster);
                }
            }
        }

        // if (RegistrationEnum::CLUSTERS === $bikeRide->getBikeRideType()->getRegistration() && 1 < $this->selectableClusterCount($bikeRide, $clusters)) {
        //     return $userCluster;
        // }

        if (RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration()) {
            $isSchoolActivity = $bikeRide->isSchoolActivity();
            foreach ($bikeRide->getClusters() as $cluster) {
                if (null !== $cluster->getLevel() && $cluster->getLevel() === $user->getLevel()) {
                    $participants = ($isSchoolActivity)
                        ? $cluster->getSessions()->map(fn(Session $session) => !$session->getMember()->isFramer())
                        : $cluster->getSessions();
                    if ($cluster->getMaxUsers() < $participants->count()) {
                        return ClusterResolverResult::failure(ClusterResolverStatus::CAPACITY_EXCEEDED, $cluster);
                    }

                    return ClusterResolverResult::success($cluster);
                }
            }
        }

        if (null === $userCluster) {
            foreach ($bikeRide->getClusters() as $cluster) {
                if ('ROLE_FRAME' !== $cluster->getRole()) {
                    return ClusterResolverResult::success($cluster);
                }
            }
        }

        $clusters = $bikeRide->getClusters();
        if (0 < $clusters->count()) {
            return new ClusterResolverResult($clusters->first());
        }
        
        return ClusterResolverResult::failure(ClusterResolverStatus::NO_CLUSTER_AVAILABLE);
    }
}