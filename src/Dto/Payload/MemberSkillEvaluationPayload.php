<?php

declare(strict_types=1);

namespace App\Dto\Payload;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\MemberSkill;

readonly class MemberSkillEvaluationPayload
{
    public function __construct(
        public MemberSkill $memberSkill,
        public EvaluationEnum $evaluation,
        public string $token,
    ) {
    }
}
