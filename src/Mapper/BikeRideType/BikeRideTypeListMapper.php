<?php

declare(strict_types=1);

namespace App\Mapper\BikeRideType;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ActivityFilter;
use App\Dto\Filter\BikeRideTypeFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Entity\BikeRideType;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BikeRideTypeListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        BikeRideTypeFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        $items = [];

        /** @var BikeRideType $entity */
        foreach ($entities as $entity) {
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                dropdown: $this->dropDown($entity, $referer),
            );
        }

        return new ListView(
            name: 'bike_ride_type',
            title: 'Type d\'activité',
            description: 'Administration des types d\'activité.',
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
            addItem: new ButtonView(
                label: 'Ajouter un type d\'activité',
                url: $this->urlGenerator->generate('admin_bike_ride_type_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
        );
    }

    private function settings(string $referer): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('BIKE_RIDE_TYPE', $referer, RoundedVariant::ROUNDED, [
            new ButtonView(
                label: 'Ajouter un message',
                url: $this->urlContextService->generateUrl('admin_message_add', [
                    'sectionName' => 'BIKE_RIDE_TYPE'
                ], $referer),
                icon: 'lucide:message-circle-plus',
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function dropDown(BikeRideType $entity, string $referer): DropdownView
    {
        return  new DropdownView(
            menuItems: [
                new ButtonView(
                    label: 'Modifier',
                    url: $this->urlContextService->generateUrl('admin_bike_ride_type_edit', [
                        'bikeRideType' => $entity->getId()
                        ], $referer),
                    icon: 'lucide:pencil',
                    variant: ColorVariant::DROPDOWN,
                ),
            ]
        );
    }
}
