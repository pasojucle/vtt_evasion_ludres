<?php

declare(strict_types=1);

namespace App\State\MemberSkill\Provider;

use App\Dto\Filter\MemberSkillFilter;
use App\Dto\State\TurboStreamContext;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Dto\View\SheetView;
use App\Service\PaginatorService;
use App\State\Interface\ListLoadMoreProviderInterface;

class MemberSkillReadProvider extends AbstractMemberSkillProvider implements ListLoadMoreProviderInterface
{
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
            memberSkillDevelopmentData: $this->getMemberSkillDevelopmentData($member),
            route: $context->route,
            currentPage: $currentPage,
        );
    }

    public function getFormView(object $entity, ?string $fallback = null): SheetView
    {
        return new SheetView(
            title: 'Modifier',
            description: 'Modifier le filtre de recherche',
            action: 'Modifier'
        );
    }

    public function getFormOptions(object $entity): array
    {
        $filterConfig = $this->getFilterConfig('admin_member_skill_filter');

        return [
            'data_class' => $filterConfig->getDataClass(),
            'fields' => $filterConfig->getFields(),
            'advanced_fields' => $filterConfig->getAdvancedFields(),
        ];
    }
}
