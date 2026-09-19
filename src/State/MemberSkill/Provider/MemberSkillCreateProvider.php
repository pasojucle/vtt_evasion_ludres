<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\MemberSkillFilter;
use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\State\ViewContext;
use App\Dto\View\MemberSkill\MemberSkillSheetView;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\Service\PaginatorService;
use App\State\Interface\ListLoadMoreProviderInterface;
use App\State\MemberSkill\Trait\MemberSkillDataProviderTrait;

class MemberSkillCreateProvider implements ListLoadMoreProviderInterface
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
     * @param MemberSkillCreatePayload $entity
     */
    public function getStreamView(object $entity, ?ViewContext $context = null): MemberSkillsView
    {
        $currentPage = $context->page;
        /**  @var MemberSkillFilter $filter */
        $filter = $context->filters;
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
            route: $context->route,
            currentPage: $currentPage,
        );
    }

    public function getFormView(object $entity, ?ViewContext $context = null): MemberSkillSheetView
    {
        return new MemberSkillSheetView(
            title: 'Ajouter',
            description: 'Ajouter une compétence',
            action: 'Ajouter'
        );
    }

    /**
     * @param MemberSkillCreatePayload $entity
     */
    public function getFormOptions(object $entity, ?ViewContext $context = null): array
    {
        return [
            'memberId' => $context->parent->getId(),
        ];
    }
}
