<?php

declare(strict_types=1);

namespace App\Mapper\SecondHand;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SecondHandFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\SecondHand;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
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
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

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
                dropdown: $this->dropDown($entity, $targetUrl),
                url: $this->urlContextService->generateUrl("admin_second_hand_show", ['secondHand' => $entity->getId()], $targetUrl),
                gridTemplateBadges: 'grid-cols-2',
            );
        }

        return new ListView(
            name: 'second_hand',
            title: 'Annonces d\'occasion',
            description: 'Administration des annonces d\'occasion.',
            items: $items,
            settings: $this->settings($targetUrl),
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
        );
    }

    private function settings(string $targetUrl): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('SECOND_HAND', $targetUrl, RoundedVariant::ROUNDED, [
            new LinkView(
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

    private function dropDown(SecondHand $entity, string $targetUrl): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_second_hand_edit', ['secondHand' => $entity->getId()], $targetUrl),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
                 new LinkView(
                     label: 'Supprimer',
                     url: $this->urlContextService->generateUrl('admin_second_hand_delete', ['secondHand' => $entity->getId()], $targetUrl),
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
}
