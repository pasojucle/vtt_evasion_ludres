<?php

declare(strict_types=1);

namespace App\State\Dashboard\Provider;

use App\Dto\View\Dashboard\DashboardListView;
use App\Mapper\Dashboard\DashboardOrderMapper;
use App\Repository\OrderHeaderRepository;

class DashboardOrderProvider
{
    public function __construct(
        private OrderHeaderRepository $orderRepository,
        private DashboardOrderMapper $dashboardOrderMapper,
    ) {
    }

    public function getCollection(): DashboardListView
    {
        return $this->dashboardOrderMapper->mapToView(
            $this->orderRepository->countPendingOrdersByStatus()
        );
    }
}
