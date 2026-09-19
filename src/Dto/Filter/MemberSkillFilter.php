<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\Level;
use App\Entity\SkillCategory;

class MemberSkillFilter extends AbstractFilter
{
    public function __construct(
        public ?EvaluationEnum $evaluation = null,
        public ?SkillCategory $category = null,
        public ?Level $level = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
        public ?int $page = null,
    ) {
    }
}
