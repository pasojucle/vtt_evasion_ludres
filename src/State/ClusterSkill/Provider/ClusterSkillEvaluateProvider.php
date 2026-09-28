<?php

declare(strict_types=1);

namespace App\State\ClusterSkill\Provider;

use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Dto\View\ClusterSkill\ClusterSkillMembersView;
use App\Entity\MemberSkill;
use App\Mapper\ClusterSkill\ClusterSkillMemberMapper;

class ClusterSkillEvaluateProvider implements TurboStreamProviderInterface
{

    public function __construct(
        private ClusterSkillMemberMapper $clusterSkillMemberMapper,
    ) {
    }

    /**
     * @param MemberSkill $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): ClusterSkillMembersView
    {
        return $this->clusterSkillMemberMapper->mapToView($data->getMember(), $data->getSkill(), $data);
    }
}
