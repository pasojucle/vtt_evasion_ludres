<?php

declare(strict_types=1);

namespace App\Dto\View\Dashboard;

readonly class DashboardWidgetListView
{
    public function __construct(
        public string $id,
        public string $title,
        public string $frameUrl,
        public string $url,
    ) {
    }
}
