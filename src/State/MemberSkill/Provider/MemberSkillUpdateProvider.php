<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberSkill\MemberSkillUpdateView;
use App\Entity\MemberSkill;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\State\MemberSkill\Trait\MemberSkillDataProviderTrait;

class MemberSkillUpdateProvider
{
    use MemberSkillDataProviderTrait;

    public function __construct(
        protected MemberSkillReadMapper $memberSkillReadMapper,
        protected MemberSkillRepository $memberSkillRepository,
        protected SkillRepository $skillRepository,
        protected SkillCategoryRepository $skillCategoryRepository,
        protected MemberSkillUpdateMapper $memberSkillUpdateMapper,
    ) {
    }
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
