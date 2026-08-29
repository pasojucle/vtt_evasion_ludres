<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberSkill\MemberSkillUpdateView;
use App\Entity\MemberSkill;

class MemberSkillUpdateProvider extends AbstractMemberSkillProvider
{
    /**
     * @param MemberSkill $entity
     */
    public function getStreamView(object $entity, ?TurboStreamContext $context = null): MemberSkillUpdateView
    {
        $member = $entity->getMember();

        return $this->memberSkillUpdateMapper->mapToView(
            memberSkill: $entity,
            memberSkillDevelopmentData: $this->getMemberSkillDevelopmentData($member),
        );
    }
}
