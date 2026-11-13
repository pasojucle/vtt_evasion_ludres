<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Core\Contract\Provider\ListLoadMoreProviderInterface;
use App\Core\Dto\FlashMessage;
use App\Core\Dto\HandlerContext;
use App\Core\Filter\FilterHydratorTrait;
use App\Dto\Filter\MemberSkillFilter;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Dto\View\SheetView;
use App\Mapper\MemberSkill\MemberSkillReadMapper;
use App\Mapper\MemberSkill\MemberSkillUpdateMapper;
use App\Repository\MemberSkillRepository;
use App\Repository\SkillCategoryRepository;
use App\Repository\SkillRepository;
use App\Service\PaginatorService;
use App\State\MemberSkill\Trait\MemberSkillDataProviderTrait;

/**
 * @implements ListLoadMoreProviderInterface<MemberSkillFilter>
 */
class MemberSkillReadProvider implements ListLoadMoreProviderInterface
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

    public function getStreamView(object $data, ?FlashMessage $flashMessage = null, ?HandlerContext $context = null): MemberSkillsView
    {
        $currentPage = $context->page;
        $member = $context->parent;
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        $qb = $this->getQueryBuilder($member, $data);

        return $this->memberSkillReadMapper->mapToView(
            member: $member,
            filter: $data,
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

    public function getView(object $data, ?HandlerContext $context = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le filtre de recherche',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity, ?HandlerContext $context = null): array
    {
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        return [
            'data_class' => $filterConfig->getDataClass(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
        ];
    }
}
