<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\SecondHandStateEnum;
use App\Entity\Member;

class SecondHandFilter extends AbstractFilter
{
    public function __construct(
        public ?SecondHandStateEnum $state = null,
        public ?Member $member = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }

    public function setDefaultState(): void
    {
        $this->state = SecondHandStateEnum::DRAFT;
    }
}
