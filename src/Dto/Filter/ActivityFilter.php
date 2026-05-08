<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\ActivityPeriod;

class ActivityFilter extends AbstractFilter
{
    public function __construct(
        public ?ActivityPeriod $period = null,
        public ?string $month = null,
    ) {
    }
}
