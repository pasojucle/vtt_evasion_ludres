<?php

declare(strict_types=1);

namespace App\Mapper\MemberSkill;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\MemberSkillFilter;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LinkView;
use App\Dto\View\MemberSkill\MemberSkillsView;
use App\Entity\MemberSkill;
use App\Mapper\FilterChipsMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\PaginatorService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MemberSkillReadMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private MemberSkillMapper $memberSkillMapper,
        private FilterChipsMapper $filterChipsMapper,
    ) {
    }

    public function mapToView(
        MemberSkillFilter $filter,
        FilterConfigInterface $filterConfig,
        Paginator $paginatedSkills,
        string $route,
        int $currentPage,
    ): MemberSkillsView {
        $member = $filter->member;
        $queriyParams = $filter->toArray();

        $hasMoreSkills = $currentPage * PaginatorService::PAGINATOR_PER_PAGE < $paginatedSkills->count();

        return new MemberSkillsView(
            memberId: $member->getId(),
            queries: $filter->toArray(),
            category: $filter->category?->getName() ?? 'Toutes les catégories',
            level: $filter->level?->getTitle() ?? 'Tous les niveaux',
            filterChips: $this->filterChipsMapper->mapToView(
                $filter,
                'admin_member_skill_filter_delete',
                $filterConfig->getAdvancedFields(),
                sprintf('member-skills-%s', $member->getId())
            ),
            filterAction: new LinkView(
                url: $this->urlGenerator->generate('admin_member_skill_filter', $queriyParams),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
            addAction: new LinkView(
                url: $this->urlGenerator->generate('admin_member_skill_add', $queriyParams),
                icon: 'lucide:plus',
                label: 'Ajouter',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                ],
            ),
            loadMoreAction: ($hasMoreSkills)
                ? new LinkView(
                    label: 'Afficher plus',
                    url: $this->urlGenerator->generate('admin_member_skill_list', array_merge($queriyParams, ['page' => $currentPage + 1])),
                    variant: ColorVariant::OUTLINE,
                    icon: 'lucide:chevron-down',
                )
                : null,
            counter: $paginatedSkills->count(),
            skills: array_map(
                fn (MemberSkill $memberSkill) => $this->memberSkillMapper->mapToView($memberSkill),
                iterator_to_array($paginatedSkills)
            ),
        );
    }
}
