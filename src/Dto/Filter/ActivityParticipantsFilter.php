<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\BikeRide;
use DateTimeImmutable;

class ActivityParticipantsFilter extends AbstractFilter
{
    public function __construct(
        public ?BikeRide $activity,
        public DateTimeImmutable $startAt,
        public DateTimeImmutable $endAt,
    ) {
    }
}
