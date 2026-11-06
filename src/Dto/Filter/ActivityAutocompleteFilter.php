<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\BikeRide;
use DateTimeImmutable;

class ActivityAutocompleteFilter extends AbstractFilter
{
    public function __construct(
        public ?BikeRide $activity = null,
        public ?DateTimeImmutable $startAt = null,
        public ?DateTimeImmutable $endAt = null,
    ) {
    }
}
