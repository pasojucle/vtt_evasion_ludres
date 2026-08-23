<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Level;
use App\Entity\Member;
use App\Entity\Skill;
use App\Entity\SkillCategory;

class MemberSkillCreatePayload
{
    public function __construct(
        public Member $member,
        public ?Skill $skill = null,
        public ?SkillCategory $category = null,
        public ?Level $level = null,
    ) {
    }
}
