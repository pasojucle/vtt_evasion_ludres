<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\AvailabilityEnum;
use App\Entity\Enum\LevelType;
use App\Entity\Member;

class ActivityFramersFilter extends AbstractFilter
{
    public LevelType $levelType;
    public array $levels;
    public function __construct(
        public ?AvailabilityEnum $availability = AvailabilityEnum::AVAILABLE,
        public ?Member $member = null,
    ) {
        $this->levelType = LevelType::FRAME;
        $this->levels = [LevelType::FRAME];
    }
}
