<?php

declare(strict_types=1);

namespace App\Mapper\MemberSkill;

use App\Dto\View\MemberSkill\MemberSkillUpdateView;
use App\Entity\MemberSkill;

class MemberSkillUpdateMapper
{
    public function __construct(
        private MemberSkillMapper $memberSkillMapper,
        private MemberSkillDevelopmentMapper $memberSkillDevelopmentMapper,
    ) {
    }

    public function mapToView(
        MemberSkill $memberSkill,
        array $memberSkillDevelopmentData,
    ): MemberSkillUpdateView {
        $member = $memberSkill->getMember();

        return new MemberSkillUpdateView(
            memberId: $member->getId(),
            memberSkill: $this->memberSkillMapper->mapToView($memberSkill),
            memberSkillDevelopment: $this->memberSkillDevelopmentMapper->mapToView($memberSkillDevelopmentData),
        );
    }
}
