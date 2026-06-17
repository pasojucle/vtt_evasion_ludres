<?php

declare(strict_types=1);

namespace App\Mapper\SkillCategory;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SkillCategoryFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\SkillCategory;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SkillCategoryListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        SkillCategoryFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $items = [];

        /** @var SkillCategory $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                indicators: $this->getIndicators($entity),
                dropdown: $this->dropDown($entity),
                gridTemplateContent: 'grid-cols-[1fr_50px]',
            );
        }

        return new ListView(
            id: 'skill_categories_contrainer',
            title: 'Catégories',
            description: 'Administration des catégories de compétence.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            addItem: new ButtonView(
                label: 'Ajouter une catégorie',
                url: $this->urlGenerator->generate('admin_skill_category_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
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

    private function dropDown(SkillCategory $entity): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new ButtonView(
                    label: 'Modifier',
                    url: $this->urlGenerator->generate('admin_skill_category_edit', ['skillCategory' => $entity->getId()]),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new ButtonView(
                     label: 'Supprimer',
                     url: $this->urlGenerator->generate('admin_skill_category_delete', ['skillCategory' => $entity->getId()]),
                     icon: 'lucide:delete',
                     variant: ColorVariant::DROPDOWN,
                     htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                 )
            ]
        );
    }
}
