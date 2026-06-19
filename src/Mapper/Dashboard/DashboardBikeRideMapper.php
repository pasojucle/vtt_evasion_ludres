<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\Enum\Size;
use App\Dto\View\BadgeView;
use App\Dto\View\Dashboard\DashboardBikeRideView;
use App\Dto\View\Dashboard\DashboardClusterView;
use App\Entity\BikeRide;
use App\Entity\Enum\LevelType;
use App\Entity\Enum\RegistrationEnum;

class DashboardBikeRideMapper
{
    public function mapToView(BikeRide $entity, array $sessions): DashboardBikeRideView
    {
        return new DashboardBikeRideView(
            $entity->getId(),
            $this->getClusters($entity, $sessions)
        );
    }

    private function getClusters(BikeRide $bikeRide, array $sessions): array
    {
        $isSchoolBikeRide = RegistrationEnum::SCHOOL === $bikeRide->getBikeRideType()->getRegistration();
        $sessionsByClusters = [];
        foreach ($sessions as $session) {
            $clusterId = ($isSchoolBikeRide && LevelType::FRAME === $session->getMember()->getLevel()->getType())
                ? 'framers'
                : $session->getCluster()->getId();
            $sessionsByClusters[$clusterId][] = $session;
        }
        $clusters = [];
        foreach ($bikeRide->getClusters() as $cluster) {
            $clusterId = ($isSchoolBikeRide && !$cluster->getLevel())
                    ? 'framers'
                    : $cluster->getId();
            $sessions = (array_key_exists($clusterId, $sessionsByClusters))
                ? $sessionsByClusters[$cluster->getId()]
                : [];
            $clusters[] = new DashboardClusterView(
                $cluster->getTitle(),
                new BadgeView(
                    value: (string) count($sessions),
                    color: $cluster->getLevel()?->getColor(),
                    size: Size::MD,
                )
            );
        }

        return $clusters;
    }
}
