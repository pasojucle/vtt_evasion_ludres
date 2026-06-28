<?php

declare(strict_types=1);

namespace App\Mapper\SecondHand;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
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
use App\Service\UrlContextService;
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
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        SecondHandFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

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
                dropdown: $this->dropDown($entity, $referer),
                url: $this->urlContextService->generateUrl("admin_second_hand_show", ['secondHand' => $entity->getId()], $referer),
                gridTemplateBadges: 'grid-cols-2',
            );
        }

        return new ListView(
            name: 'second_hand',
            title: 'Annonces d\'occasion',
            description: 'Administration des annonces d\'occasion.',
            items: $items,
            settings: $this->settings($referer),
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

    private function settings(string $referer): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('SECOND_HAND', $referer, RoundedVariant::ROUNDED, [
            new ButtonView(
                label: 'Catégories',
                url: $this->urlGenerator->generate('admin_second_hand_category_list'),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function getIndicators(SecondHand $entity): array
    {
        return [
            new BadgeView(
                value: $entity->getCategory()->getIcon(),
                variant: ColorVariant::ACCENT,
                size: Size::ICON,
            )
        ];
    }

    private function dropDown(SecondHand $entity, string $referer): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new ButtonView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_second_hand_edit', ['secondHand' => $entity->getId()], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new ButtonView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_second_hand_delete', ['secondHand' => $entity->getId()], $referer),
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
