<?php

declare(strict_types=1);

namespace App\State\Dashboard\Provider;

use App\Dto\View\Dashboard\DashboardItemView;
use App\Mapper\Dashboard\DashboardCronTabMapper;
use App\Service\ProjectDirService;
use App\UseCase\CronTab\CronTabLog;

class DashboardCronTabProvider
{
    public function __construct(
        private DashboardCronTabMapper $dashboardCronTabMapper,
        private ProjectDirService $projectDir,
    ) {
    }

    public function getItem(): DashboardItemView
    {
        return $this->dashboardCronTabMapper->mapToView($this->filemtime());
    }

    private function filemtime(): int
    {
        $filename = $this->projectDir->path('data', CronTabLog::FILENAME);
        if (file_exists($filename)) {
            return filemtime($filename);
        }

        return 0;
    }
}
