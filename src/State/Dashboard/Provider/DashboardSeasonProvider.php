<?php

declare(strict_types=1);

namespace App\State\Dashboard\Provider;

use App\Dto\View\Dashboard\DashboardListView;
use App\Mapper\Dashboard\DashboardOrderMapper;
use App\Mapper\Dashboard\DashboardSeasonMapper;
use App\Repository\LicenceRepository;
use App\Repository\OrderHeaderRepository;
use App\Service\SeasonService;

class DashboardSeasonProvider
{
    public function __construct(
        private LicenceRepository $licenceRepository,
        private DashboardSeasonMapper $dashboardSeasonMapper,
        private SeasonService $seasonService,
    ) {
    }

    public function getCollection(): DashboardListView
    {
        return $this->dashboardSeasonMapper->mapToView(
            $this->licenceRepository->findAllByLastSeason($this->seasonService->getCurrentSeason()),
        );
    }
}
