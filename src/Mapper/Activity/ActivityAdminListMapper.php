<?php

declare(strict_types=1);

namespace App\Mapper\Activity;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ActivityFilter;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\BikeRide;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ActivityAdminListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ActivityMapper $activityMapper,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        array $participantTotalByEntity,
        string $route,
        int $currentPage,
        ActivityFilter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'activity',
            title: 'Programme des activités',
            description: 'Administration des activités : création, modification.',
            items: array_map(
                fn (BikeRide $activity) =>
                $this->activityMapper->mapToView($activity, $participantTotalByEntity, $targetUrl),
                iterator_to_array($entities)
            ),
            dropdown: $this->settings($targetUrl),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChipViews: $this->filterChipsMapper->mapToView($filter, $filterConfig->getAdvancedFields(), $filterConfig->getRouteName()),
            addItem: new LinkView(
                label: 'Ajouter une activité',
                url: $this->urlContextService->generateUrl('admin_bike_ride_add', [], $targetUrl),
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
        return $this->dropdownSettingsMapper->mapToView('BIKE_RIDE', $targetUrl, RoundedVariant::ROUNDED, [
            new LinkView(
                label: 'Types d\'activité',
                icon: 'lucide:settings',
                url: $this->urlContextService->generateUrl('admin_bike_ride_type_list', [], $targetUrl),
                variant: ColorVariant::DROPDOWN,
            ),
        ]);
    }
}
