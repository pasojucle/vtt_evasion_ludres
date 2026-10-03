<?php

declare(strict_types=1);

namespace App\UseCase\v2\MemberSkill;

use App\Entity\Enum\EvaluationEnum;
use App\Entity\Member;
use App\Entity\MemberSkill;
use App\Entity\Skill;
use App\Repository\Interface\MemberSkillRepositoryInterface;

final class CreateMemberSkill
{
    public function __construct(
        private MemberSkillRepositoryInterface $memberSkillRepository,
    ) {
    }

    public function __invoke(
        Member $member,
        Skill $skill,
        EvaluationEnum $evaluation,
    ): MemberSkill {
        $newEntity = new MemberSkill();
        $newEntity
            ->setMember($member)
            ->setSkill($skill)
            ->setEvaluation($evaluation);

        $this->memberSkillRepository->save($newEntity);

        return $newEntity;
    }
}
