<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\MemberSkill\MemberSkillUpdateView;
use App\Entity\MemberSkill;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\State\MemberSkill\Trait\MemberSkillDataProviderTrait;

class MemberSkillUpdateProvider implements TurboStreamProviderInterface
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
     * @param MemberSkill $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): MemberSkillUpdateView
    {
        $member = $data->getMember();

        return $this->memberSkillUpdateMapper->mapToView(
            memberSkill: $data,
            memberSkillDevelopmentData: $this->getMemberSkillDevelopmentData($member),
        );
    }
}
