<?php

declare(strict_types=1);

namespace App\Mapper\Participation;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ParticipationFilter;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ParticipationListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private ParticipationMapper $participationMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        ParticipationFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $arrayFilter = $filter->toArray();

        return new ListView(
            name: 'participation',
            title: 'Participation',
            description: 'Liste de la participation des activités.',
            items: array_map(function (array $participations) use ($arrayFilter) {
                $activity = $participations['activity'];
                $total = $participations['total'];
                $params = ['activity' => $activity->getId()];
                return $this->participationMapper->mapToView($activity, $total, array_merge($params, $arrayFilter));
            }, iterator_to_array($entities)),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            tools: $this->tools($filter),
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
        );
    }

    public function tools(ParticipationFilter $filter): ?DropdownView
    {
        return new DropdownView(
            variant: DropdownVariant::GOST,
            menuItems: [
                new LinkView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_participation_export', $filter->toArray()),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                        new HtmlAttributeView('data-turbo', 'false')
                    ],
                )
            ],
        );
    }
}
