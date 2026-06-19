<?php

declare(strict_types=1);

namespace App\Dto\View\Dashboard;

readonly class DashboardListView
{
    /**
     * @param string $id
     * @param DashboardItemView[] $items
     * @param DashboardItemView[] | null $parameters
     */
    public function __construct(
        public string $id,
        public array $items,
        public ?array $parameters = null,
    ) {
    }
}
