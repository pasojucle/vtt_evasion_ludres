<?php

declare(strict_types=1);

namespace App\Dto\View\Dashboard;

readonly class DashboardWidgetParticipationView
{
    public function __construct(
        public string $title,
        public int $isSchool,
    ) {
    }
}
