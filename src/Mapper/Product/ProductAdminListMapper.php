<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Core\Contract\Filter\FilterConfigInterface;
use App\Core\Dto\HandlerContext;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ProductFilter;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LinkView;
use App\Dto\View\ListView;
use App\Entity\Product;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductAdminListMapper
{
    public function __construct(
        private ProductMapper $productMapper,
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private UrlGeneratorInterface $urlGenerator,
        private WikiMapper $wikiMapper,
        private UrlContextService $urlContextService,
    ) {
    }

    public function mapToView(Paginator $entities, HandlerContext $context, ProductFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $route = $context->route;
        $currentPage = $context->page;
        $targetUrl = $this->urlContextService->encodeUrl($route, $filter->toQueryParams($currentPage));
    
        return new ListView(
            name: 'product',
            title: 'Boutique',
            description: 'Administration des produits aux couleurs du club vendus en lignes.',
            items: array_map(fn (Product $product) => $this->productMapper->mapToView($product, $targetUrl), iterator_to_array($entities)),
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new LinkView(
                label: 'Ajouter un produit',
                url: $this->urlContextService->generateUrl('admin_product_add', [], $targetUrl),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
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
            wiki:  $this->wikiMapper->mapToView('boutique'),
        );
    }
}
