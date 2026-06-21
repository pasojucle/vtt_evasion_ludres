<?php

declare(strict_types=1);

namespace App\Mapper\Dashboard;

use App\Dto\View\BadgeView;
use App\Dto\View\Dashboard\DashboardItemView;
use App\Dto\View\Dashboard\DashboardListView;
use App\Entity\Enum\SecondHandStateEnum;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardSecondHandMapper
{
    public function __construct(
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param array<int, array{count: int, state: SecondHandStateEnum}> $countPendingSecondHandByState
     */
    public function mapToView(array $countPendingSecondHandByState): DashboardListView
    {
        $items = [];
        foreach ($countPendingSecondHandByState as $countPendingSecondHand) {
            $items[] = new DashboardItemView(
                label: sprintf('%ss', $countPendingSecondHand['state']->trans($this->translator)),
                badge: new BadgeView(
                    (string) $countPendingSecondHand['count']
                )
            );
        }

        return new DashboardListView(
            id: 'dashboard-second-hands',
            items: $items
        );
    }
}
