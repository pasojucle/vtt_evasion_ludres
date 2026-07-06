<?php

declare(strict_types=1);

namespace App\Dto\Filter;

class SkillCategoryFilter extends AbstractFilter
{
    public function __construct(
        public ?string $name = null,
        public ?bool $showDeleted = null,
        public string $sort = 'ASC',
        public ?int $page = null,
    ) {
    }
}
