<?php

declare(strict_types=1);

namespace App\Mapper\SkillCategory;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SkillCategoryFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\SkillCategory;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SkillCategoryListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        SkillCategoryFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var SkillCategory $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                indicators: $this->getIndicators($entity),
                dropdown: $this->dropDown($entity, $referer),
                status: $this->getStatus($entity),
                isDeleted: $entity->isDeleted(),
                gridTemplateContent: 'grid-cols-[1fr_80px] lg:grid-cols-[1fr_150px]',
                gridTemplateBadges: 'grid-cols-1 lg:grid-cols-[1fr_2fr] gap-2 justify-items-center',
            );
        }

        return new ListView(
            name: 'skill_category',
            title: 'Catégories',
            description: 'Administration des catégories de compétence.',
            items: $items,
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
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getRouteName(), $filterConfig->getAdvancedFields()),
            addItem: new LinkView(
                label: 'Ajouter une catégorie',
                url: $this->urlGenerator->generate('admin_skill_category_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
        );
    }

    private function getIndicators(SkillCategory $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON
            )
        ];
    }

    private function dropDown(SkillCategory $entity, string $referer): DropdownView
    {
        if ($entity->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_skill_category_restore', [
                            'skillCategory' => $entity->getId()
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
                    url: $this->urlContextService->generateUrl('admin_skill_category_edit', ['skillCategory' => $entity->getId()], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                ),
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('admin_skill_category_delete', ['skillCategory' => $entity->getId()], $referer),
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

    private function getStatus(SkillCategory $entity): ?BadgeView
    {
        if ($entity->isDeleted()) {
            return new BadgeView('Supprimée', ColorVariant::DESTRUCTIVE);
        }

        return null;
    }
}
