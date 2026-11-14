<?php

declare(strict_types=1);

namespace App\Mapper\SecondHandCategory;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\SecondHandCategoryFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\SecondHandCategory;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecondHandCategoryListMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private FilterChipsMapper $filterChipsMapper,
        private PaginatorMapper $paginatorMapper,
        private UrlContextService $urlContextService,
        private SecondHandCategoryMapper $secondHandCategoryMapper,
    ) {
    }

    public function mapToView(
        Paginator $entities,
        HandlerContext $context,
        SecondHandCategoryFilter $filter,
        FilterConfigInterface $filterConfig
    ): ListView {
        $currentPage = $context->page;
        $route = $context->route;
        $targetUrl = $this->urlContextService->encodeUrl($route, $filter->toQueryParams($currentPage));

        return new ListView(
            name: 'second_hand_category',
            title: 'Catégories',
            description: 'Administration des catégories des annonces d\'occasion.',
            items: array_map(fn (SecondHandCategory $category) =>
                $this->secondHandCategoryMapper->mapToView($category, $targetUrl), iterator_to_array($entities)),
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
                label: 'Ajouter une catégorie',
                url: $this->urlContextService->generateUrl('admin_second_hand_category_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close'),
                ],
            ),
        );
    }
}
