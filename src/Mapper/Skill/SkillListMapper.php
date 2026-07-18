<?php

declare(strict_types=1);

namespace App\Mapper\Skill;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SkillFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\Skill;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SkillListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        SkillFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var Skill $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getContent()),
                ],
                indicators: $this->getIndicators($entity),
                status: $this->getStatus($entity),
                isDeleted: $entity->isDeleted(),
                dropdown: $this->dropDown($entity, $referer),
                gridTemplateContent: 'grid-cols-[1fr_80px] lg:grid-cols-[1fr_150px]',
                gridTemplateBadges: 'grid-cols-1 lg:grid-cols-[1fr_2fr] gap-2 justify-items-center',
            );
        }

        return new ListView(
            name: 'skill',
            title: 'Compétences',
            description: 'Administration de la liste des compétences.',
            items: $items,
            settings: $this->settings($referer),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            addItem: new LinkView(
                label: 'Ajouter une compétence',
                url: $this->urlGenerator->generate('admin_skill_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    private function settings(string $referer): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView(null, $referer, RoundedVariant::ROUNDED, [
            new LinkView(
                label: 'Catégories',
                url: $this->urlGenerator->generate('admin_skill_category_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function dropDown(Skill $entity, string $referer): DropdownView
    {
        if ($entity->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_skill_restore', [
                            'skill' => $entity->getId()
                            ], $referer),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_skill_edit', ['skill' => $entity->getId()], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new LinkView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_skill_delete', ['skill' => $entity->getId()], $referer),
                     icon: 'lucide:delete',
                     variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                 )
            ]
        );
    }

    private function getIndicators(Skill $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getCategory()->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            )
        ];
    }

    private function getStatus(Skill $entity): ?BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
