<?php

declare(strict_types=1);

namespace App\Mapper\ActivityType;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\BikeRideTypeFilter;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\BikeRideType;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityTypeListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private ActivityTypeMapper $ActivityTypeMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        string $route,
        int $currentPage,
        BikeRideTypeFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'bike_ride_type',
            title: 'Type d\'activité',
            description: 'Administration des types d\'activité.',
            items: $this->listItems($entities, $targetUrl),
            settings: $this->settings($targetUrl),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            addItem: new LinkView(
                label: 'Ajouter un type d\'activité',
                url: $this->urlContextService->generateUrl('admin_bike_ride_type_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
        );
    }

    private function settings(string $targetUrl): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('BIKE_RIDE_TYPE', $targetUrl, RoundedVariant::ROUNDED, [
            new LinkView(
                label: 'Ajouter un message',
                url: $this->urlContextService->generateUrl('admin_message_add', [
                    'sectionName' => 'BIKE_RIDE_TYPE'
                ], $targetUrl),
                icon: 'lucide:message-circle-plus',
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }

    private function listItems(Paginator $bikeRideTypes, string $targetUrl): array
    {
        $items = [];

        /** @var BikeRideType $bikeRideType */
        foreach ($bikeRideTypes as $bikeRideType) {
            $items[] = $this->ActivityTypeMapper->mapToView($bikeRideType, $targetUrl);
        }

        return $items;
    }
}
