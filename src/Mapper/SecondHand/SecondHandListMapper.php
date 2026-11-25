<?php

declare(strict_types=1);

namespace App\Mapper\SecondHand;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\RoundedVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SecondHandFilter;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\SecondHand;
use App\Mapper\DropdownSettingsMapper;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecondHandListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SecondHandMapper $secondHandMapper,
        private DropdownSettingsMapper $dropdownSettingsMapper,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        SecondHandFilter $filter,
        FilterConfigInterface $filterConfig,
    ): ListView {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));


        return new ListView(
            name: 'second_hand',
            title: 'Annonces d\'occasion',
            description: 'Administration des annonces d\'occasion.',
            items: array_map(fn (SecondHand $entity) => $this->secondHandMapper->mapToView($entity, $targetUrl), iterator_to_array($entities)),
            dropdown: $this->dropdown($targetUrl),
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
        );
    }

    private function dropdown(string $targetUrl): DropdownView
    {
        return $this->dropdownSettingsMapper->mapToView('SECOND_HAND', $targetUrl, RoundedVariant::ROUNDED, [
            new LinkView(
                label: 'Catégories',
                url: $this->urlGenerator->generate('admin_second_hand_category_list'),
                variant: ColorVariant::DROPDOWN,
                icon: 'lucide:settings',
            ),
        ]);
    }
}
