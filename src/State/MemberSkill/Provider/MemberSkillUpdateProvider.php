<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberSkill\MemberSkillView;
use App\Mapper\MemberSkill\MemberSkillMapper;

class MemberSkillUpdateProvider
{
    public function __construct(
        private MemberSkillMapper $memberSkillMapper
    ) {
    }


    public function getStreamView(object $entity, ?TurboStreamContext $context = null): MemberSkillView
    {
        return $this->memberSkillMapper->mapToView($entity);
    }
}
