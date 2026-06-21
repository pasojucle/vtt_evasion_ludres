<?php

declare(strict_types=1);

namespace App\State\Dashboard\Provider;

use App\Dto\View\Dashboard\DashboardListView;
use App\Mapper\Dashboard\DashboardOrderMapper;
use App\Mapper\Dashboard\DashboardSecondHandMapper;
use App\Repository\SecondHandRepository;

class DashboardSecondHandProvider
{
    public function __construct(
        private SecondHandRepository $secondHandRepository,
        private DashboardSecondHandMapper $dashboardSecondHandMapper,
    ) {
    }

    public function getCollection(): DashboardListView
    {
        return $this->dashboardSecondHandMapper->mapToView(
            $this->secondHandRepository->countPendingSencondHandByState()
        );
    }
}
