<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\LevelType;
use App\Entity\Section;

class MessageFilter extends AbstractFilter
{
    public function __construct(
        public ?Section $section = null,
        public ?string $label = null,
        public ?LevelType $levelType = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
        public ?int $page = null,
    ) {
    }
}
