<?php

declare(strict_types=1);

namespace App\UseCase\v2\MemberSkill;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\MemberSkill;
use App\Repository\Interface\MemberSkillRepositoryInterface;

final class EvaluateMemberSkill
{
    public function __construct(
        private MemberSkillRepositoryInterface $memberSkillRepository,
    ){}

    public function __invoke(
        MemberSkill $memberSkill,
        EvaluationEnum $evaluation,
    ): MemberSkill {
        $memberSkill
            ->setEvaluation($evaluation);

        $this->memberSkillRepository->save($memberSkill);

        return $memberSkill;
    }
}
