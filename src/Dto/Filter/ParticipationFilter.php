<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\BikeRideType;
use App\Entity\Enum\PracticeEnum;
use DateTimeImmutable;

class ParticipationFilter extends AbstractFilter
{
    /**
     * @param array<int|string> $levels
     * @param ?int $itemsPerPage
     */
    public function __construct(
        public array $levels = [],
        public ?DateTimeImmutable $startAt = null,
        public ?DateTimeImmutable $endAt = null,
        public ?BikeRideType $type = null,
        public ?PracticeEnum $practice = null,
        public ?int $itemsPerPage = null,
        public ?int $page = null,
    ) {
    }

    public function setDefaultPeriod(DateTimeImmutable $startAt, DateTimeImmutable $endAt): void
    {
        $this->startAt = $startAt;
        $this->endAt = $endAt;
    }
}
