<?php

declare(strict_types=1);

namespace App\State\Dashboard\Provider;

use App\Dto\View\Dashboard\DashboardWidgetsView;
use App\Mapper\Dashboard\DashboardWidgetsMapper;
use App\Repository\BikeRideRepository;

class DashboardWidgetsProvider
{
    public function __construct(
        private DashboardWidgetsMapper $dashboardWidgetsMapper,
        private BikeRideRepository $bikeRideRepository,
    ) {
    }

    public function getWidgets(): DashboardWidgetsView
    {
        return $this->dashboardWidgetsMapper->mapToView(
            $this->bikeRideRepository->findNextBikeRides(),
        );
    }
}
