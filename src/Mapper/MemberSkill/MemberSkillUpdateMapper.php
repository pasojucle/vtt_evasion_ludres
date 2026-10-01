<?php

declare(strict_types=1);

namespace App\Mapper\MemberSkill;

use App\Core\Dto\FlashMessage;
use App\Dto\View\FlashesView;
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
        ?FlashMessage $flashMessage,
    ): MemberSkillUpdateView {
        $member = $memberSkill->getMember();

        return new MemberSkillUpdateView(
            memberId: $member->getId(),
            memberSkill: $this->memberSkillMapper->mapToView($memberSkill),
            memberSkillDevelopment: $this->memberSkillDevelopmentMapper->mapToView($memberSkillDevelopmentData),
            flashesView: ($flashMessage) ? FlashesView::create($flashMessage->type, $flashMessage->messageKey) : null,
        );
    }
}
