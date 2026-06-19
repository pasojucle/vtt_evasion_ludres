<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\View\BadgeView;
use App\Dto\View\Dashboard\DashboardItemView;
use App\Dto\View\Dashboard\DashboardListView;
use App\Entity\Enum\OrderStatusEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardOrderMapper
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<int, array{count: int, status: OrderStatusEnum}> $countPendingOrdersByStatus
     */
    public function mapToView(array $countPendingOrdersByStatus): DashboardListView
    {
        $items = [];
        foreach ($countPendingOrdersByStatus as $countPendingOrders) {
            $items[] = new DashboardItemView(
                label: sprintf('%ss', $countPendingOrders['status']->trans($this->translator)),
                badge: new BadgeView(
                    (string) $countPendingOrders['count']
                )
            );
        }

        return new DashboardListView(
            id: 'dashboard-orders',
            items: $items
        );
    }
}
