<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\Payload\MemberSkillCreatePayload;
use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberSkill\MemberSkillSheetView;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\Service\PaginatorService;
use App\State\FilterHydratorTrait;
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
    public function getStreamView(object $entity, ?TurboStreamContext $context = null): MemberSkillsView
    {
        $currentPage = $context->page;
        $filter = $context->object;
        $member = $entity->member;
        $filter->member = $member;
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        $qb = $this->getQueryBuilder($filter);

        return $this->memberSkillReadMapper->mapToView(
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

    public function getFormView(object $entity, ?string $fallback = null): MemberSkillSheetView
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
    public function getFormOptions(object $entity): array
    {
        return [
            'memberId' => $entity->member->getId(),
        ];
    }
}
