<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\ActivityPeriod;
use App\Dto\Enum\ActivityRestriction;
use App\Dto\Enum\ActivityVisibility;
use App\Entity\BikeRideType;

class ActivityFilter extends AbstractFilter
{
    public function __construct(
        public ActivityPeriod $period = ActivityPeriod::UPCOMING,
        public ?string $month = null,
        public ?BikeRideType $type = null,
        public ?ActivityVisibility $visibility = null,
        public ?ActivityRestriction $restriction = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
