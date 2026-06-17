<?php

declare(strict_types=1);

namespace App\Mapper\Skill;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Filter\SkillFilter;
use App\Dto\View\ButtonView;
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
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SkillListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        SkillFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $items = [];

        /** @var Skill $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getContent()),
                ],
                dropdown: $this->dropDown($entity),
                gridTemplateContent: 'grid-cols-1',
            );
        }

        return new ListView(
            id: 'skill_contrainer',
            title: 'Compétences',
            description: 'Administration de la liste des compétences.',
            items: $items,
            settings: $this->settings(),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_skill_list'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),

            addItem: new ButtonView(
                label: 'Ajouter une compétence',
                url: $this->urlGenerator->generate('admin_skill_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    private function settings(): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView(null, RoundedVariant::ROUNDED, [
            new ButtonView(
                label: 'Catégories',
                url: $this->urlGenerator->generate('admin_skill_category_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function dropDown(Skill $entity): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new ButtonView(
                    label: 'Modifier',
                    url: $this->urlGenerator->generate('admin_skill_edit', ['skill' => $entity->getId()]),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new ButtonView(
                    label: 'Supprimer',
                    url: $this->urlGenerator->generate('admin_skill_delete', ['skill' => $entity->getId()]),
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
