<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\BikeRideType;
use App\Entity\Member;
use DateTimeImmutable;

class MemberParticipationFilter extends AbstractFilter
{
    public function __construct(
        public ?Member $member = null,
        public ?DateTimeImmutable $startAt = null,
        public ?DateTimeImmutable $endAt = null,
        public ?BikeRideType $type = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
        public ?int $page = null,
    ) {
    }
}
