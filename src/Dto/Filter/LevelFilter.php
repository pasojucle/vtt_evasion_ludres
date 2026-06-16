<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\LevelType;

class LevelFilter extends AbstractFilter
{
    public function __construct(
        public LevelType $type = LevelType::SCHOOL,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
