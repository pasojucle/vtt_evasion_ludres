<?php

declare(strict_types=1);

namespace App\Dto\View\Dashboard;

use App\Entity\BikeRide;

readonly class DashboardWidgetsView
{
    /**
     * Summary of __construct
     * @param DashboardWidgetBikeRideView[] $bikeRides
     * @param DashboardWidgetParticipationView[] $participations
     * @param DashboardWidgetListView[] $lists
     */
    public function __construct(
        public array $bikeRides,
        public array $participations,
        public array $lists,
    ) {
    }
}
