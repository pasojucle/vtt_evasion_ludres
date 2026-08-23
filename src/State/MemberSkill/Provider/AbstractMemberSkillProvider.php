<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\Filter\MemberSkillFilter;
use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Repository\MemberSkillRepository;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
use App\State\Interface\ListLoadMoreProviderInterface;
use App\State\MemberParticipation\Enum\QueryScope;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractMemberSkillProvider implements ListLoadMoreProviderInterface
{
    use FilterHydratorTrait;

    public function __construct(
        protected MemberSkillReadMapper $memberSkillReadMapper,
        protected MemberSkillRepository $memberSkillRepository,
        protected PaginatorService $paginator,
    ) {
    }

    /**
     * @param MemberSkillFilter $entity
     */
    public function getStreamView(object $entity, ?TurboStreamContext $context = null): MemberSkillsView
    {
        $currentPage = $context->page;
        $member = $context->object;
        $entity->member = $member;
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        $qb = $this->getQueryBuilder($entity);

        return $this->memberSkillReadMapper->mapToView(
            filter: $entity,
            filterConfig: $filterConfig,
            paginatedSkills: $this->paginator->paginate(
                $qb,
                $currentPage,
                PaginatorService::PAGINATOR_PER_PAGE
            ),
            route: $context->route,
            currentPage: $currentPage,
        );
    }

    protected function getQueryBuilder(MemberSkillFilter $filter, QueryScope $scope = QueryScope::LIST): QueryBuilder
    {
        $qb = $this->memberSkillRepository->getMemberSkillQuery();

        $this->memberSkillRepository->filterUser($qb, $filter->member);

        if ($filter->category) {
            $this->memberSkillRepository->filterCategory($qb, $filter->category);
        }

        if ($filter->level) {
            $this->memberSkillRepository->filterLevel($qb, $filter->level);
        }

        if ($filter->sort) {
            $this->memberSkillRepository->filterSort($qb, $filter->sort);
        }

        return $qb;
    }
}