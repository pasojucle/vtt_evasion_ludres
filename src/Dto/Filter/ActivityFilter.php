<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\ActivityPeriod;

class ActivityFilter extends AbstractFilter
{
    public function __construct(
        public ActivityPeriod $period = ActivityPeriod::UPCOMING,
        public ?string $month = null,
        public ?string $sort = null,
    ) {
    }
}
