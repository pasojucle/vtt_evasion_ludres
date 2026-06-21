<?php

declare(strict_types=1);

namespace App\Mapper\SecondHand;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Filter\SecondHandFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\SecondHand;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecondHandListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private TranslatorInterface $translator,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        SecondHandFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $items = [];

        /** @var SecondHand $entity */
        foreach ($entities as $entity) {
            $state = $entity->getState();
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                indicators: $this->getIndicators($entity),
                status: new BadgeView(
                    $state->trans($this->translator),
                    $state->variant(),
                ),
                dropdown: $this->dropDown($entity),
                url: $this->urlGenerator->generate("admin_second_hand_show", ['secondHand' => $entity->getId()]),
                gridTemplateBadges: 'grid-cols-2',
            );
        }

        return new ListView(
            id: 'list_contrainer',
            title: 'Titre de la page',
            description: 'description de la page.',
            items: $items,
            settings: $this->settings(),
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
        );
    }

    private function settings(): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('SECOND_HAND', RoundedVariant::ROUNDED, [
            new ButtonView(
                label: 'Catégories',
                url: $this->urlGenerator->generate('admin_category_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getIndicators(SecondHand $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getCategory()->getName(),
                variant: ColorVariant::ACCENT,
            )
        ];
    }

    private function dropDown(SecondHand $entity): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new ButtonView(
                    label: 'Modifier',
                    url: $this->urlGenerator->generate('admin_second_hand_edit', ['secondHand' => $entity->getId()]),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new ButtonView(
                     label: 'Supprimer',
                     url: $this->urlGenerator->generate('admin_second_hand_delete', ['secondHand' => $entity->getId()]),
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
