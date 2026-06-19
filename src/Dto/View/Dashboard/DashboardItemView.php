<?php

declare(strict_types=1);

namespace App\Dto\View\Dashboard;

use App\Dto\View\BadgeView;

readonly class DashboardItemView
{
    public function __construct(
        public string $label,
        public BadgeView $badge,
    ) {
    }
}
