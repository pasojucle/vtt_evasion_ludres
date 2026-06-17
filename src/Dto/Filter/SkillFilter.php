<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Level;
use App\Entity\SkillCategory;

class SkillFilter extends AbstractFilter
{
    public function __construct(
        public ?SkillCategory $category = null,
        public ?Level $level = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
