<?php

declare(strict_types=1);

namespace App\Dto\View\Dashboard;

readonly class DashboardBikeRideView
{
    /**
     * @param int $id
     * @param DashboardClusterView[] $clusters
     */
    public function __construct(
        public int $id,
        public array $clusters
    ) {
    }
}
