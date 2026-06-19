<?php

declare(strict_types=1);

namespace App\State\Dashboard\Provider;

use App\Dto\View\Dashboard\DashboardBikeRideView;
use App\Entity\BikeRide;
use App\Mapper\Dashboard\DashboardBikeRideMapper;
use App\Repository\SessionRepository;

class DashboardBikeRideProvider
{
    public function __construct(
        private SessionRepository $sessionRepository,
        private DashboardBikeRideMapper $dashboardBikeRideMapper,
    ) {
    }

    public function getItem(BikeRide $entity): DashboardBikeRideView
    {
        return $this->dashboardBikeRideMapper->mapToView(
            $entity,
            $this->sessionRepository->findByBikeRideId($entity->getId())
        );
    }
}
