<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Core\Contract\Provider\FormComponentProviderInterface;
use App\Core\Contract\Provider\ListFilteredProviderInterface;
use App\Core\Contract\Provider\TurboStreamProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\MemberSkillFilter;
use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\View\AssociateResourceSkillSheetView;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\Service\PaginatorService;
use App\State\MemberSkill\Trait\MemberSkillDataProviderTrait;

class MemberSkillCreateProvider implements FormComponentProviderInterface, ListFilteredProviderInterface, TurboStreamProviderInterface
{
    use FilterHydratorTrait;
    use MemberSkillDataProviderTrait;

    public function __construct(
        protected MemberSkillReadMapper $memberSkillReadMapper,
        protected MemberSkillRepository $memberSkillRepository,
        protected SkillRepository $skillRepository,
        protected SkillCategoryRepository $skillCategoryRepository,
        protected MemberSkillUpdateMapper $memberSkillUpdateMapper,
        protected PaginatorService $paginator,
    ) {
    }

    /**
     * @param MemberSkillCreatePayload $data
     */
    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): MemberSkillsView
    {
        $currentPage = $context->page;
        $filter = new MemberSkillFilter(
            category: $data->category,
            level: $data->level,
        );
        $member = $context->parent;
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        $qb = $this->getQueryBuilder($member, $filter);

        return $this->memberSkillReadMapper->mapToView(
            member: $member,
            filter: $filter,
            filterConfig: $filterConfig,
            paginatedSkills: $this->paginator->paginate(
                $qb,
                $currentPage,
                PaginatorService::PAGINATOR_PER_PAGE
            ),
            memberSkillDevelopmentData: $this->getMemberSkillDevelopmentData($member),
            currentPage: $currentPage,
        );
    }

    public function getView(object $data, ?HandlerContext $context = null): AssociateResourceSkillSheetView
    {
        return new AssociateResourceSkillSheetView(
            title: 'Ajouter',
            description: 'Ajouter une compétence',
            action: 'Ajouter'
        );
    }

    /**
     * @param MemberSkillCreatePayload $data
     */
    public function getFormOptions(object $data, ?HandlerContext $context = null): array
    {
        return [
            'memberId' => $context->parent->getId(),
        ];
    }
}
