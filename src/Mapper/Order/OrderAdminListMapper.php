<?php

declare(strict_types=1);

namespace App\Mapper\Order;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\OrderFilter;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\OrderHeader;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderAdminListMapper
{
    public function __construct(
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private UrlGeneratorInterface $urlGenerator,
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private WikiMapper $wikiMapper,
        private UrlContextService $urlContextService,
        private OrderAdminMapper $orderAdminMapper,
    ) {
    }

    public function mapToView(Paginator $entities, HandlerContext $context, OrderFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'orders',
            title: 'Commandes',
            description: 'Administration des commandes de la boutique: état des stocks, validation.',
            items: array_map(fn(OrderHeader $order) => $this->orderAdminMapper->mapToView($order, $targetUrl),
                iterator_to_array($entities)
            ),
            settings: $this->dropdownSettingsMapper->mapToView('ORDER', $targetUrl, RoundedVariant::ROUNDED_END),
            tools: $this->getTools($filter->toArray()),
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
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            wiki:  $this->wikiMapper->mapToView('boutique', RoundedVariant::ROUNDED_START),
        );
    }

    private function getTools(array $filter): DropdownView
    {
        $dropdown = new DropdownView(
            variant: DropdownVariant::GOST,
            menuItems: [
                new LinkView(
                    label: 'Exporter la sélection',
                    url: $this->urlGenerator->generate('admin_order_headers_export', $filter),
                    icon: 'lucide:file-down',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                        new HtmlAttributeView('data-turbo', 'false')
                    ]
                ),
            ],
        );

        return $dropdown;
    }
}
