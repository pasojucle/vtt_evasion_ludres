<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Cluster;
use App\Entity\Level;
use App\Entity\Member;
use App\Entity\SkillCategory;

class SkillAutocompleteFilter extends AbstractFilter
{
    public function __construct(
        public ?SkillCategory $category = null,
        public ?Level $level = null,
        public ?Cluster $cluster = null,
        public ?Member $member = null,
    ) {
    }
}
