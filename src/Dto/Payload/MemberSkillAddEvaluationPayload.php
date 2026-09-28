<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\Skill;

readonly class MemberSkillAddEvaluationPayload
{
    public function __construct(
        public Member $member,
        public Skill $skill,
        public EvaluationEnum $evaluation,
    ) {
    }
}
