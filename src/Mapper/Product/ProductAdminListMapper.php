<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\Size;
use App\Dto\Filter\ProductFilter;
use App\Dto\View\BadgeView;
use App\Dto\View\LinkView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\Interface\ListActionViewInterface;
use App\Dto\View\LabelView;
use App\Dto\View\ListItemView;
use App\Dto\View\ListView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Product;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\CsrfTokenService;
use App\Service\Filter\FilterConfigInterface;
use App\Service\UrlContextService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductAdminListMapper
{
    public function __construct(
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private UrlGeneratorInterface $urlGenerator,
        private WikiMapper $wikiMapper,
        private UrlContextService $urlContextService,
        private CsrfTokenService $csrfTokenService,
        private ProductStatusMapper $productStatusMapper,
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, ProductFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $referer = $this->urlContextService->generateTargetUrl($route, $filter->toQueryParams($currentPage));
        $items = [];
        /** @var Product $entity */
        foreach ($entities as $entity) {
            $tokenId = $this->csrfTokenService->getTokenId($entity);
            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                indicators: $entity->getSizes()->map(fn ($size) => new BadgeView($size->getName()))->toArray(),
                status: $this->productStatusMapper->mapToView($entity, $tokenId),
                dropdown: $this->getDropdown($entity, $referer),
                isDeleted: $entity->isDeleted(),
                action: $this->getAction($entity, $tokenId),
                url: $this->urlGenerator->generate("admin_product", ['product' => $entity->getId()]),
                gridTemplateRow: 'grid-cols-[1fr_50px]',
                gridTemplateBadges: 'grid-cols-[1fr_70px]',
            );
        }

        return new ListView(
            name: 'product',
            title: 'Boutique',
            description: 'Administration des produits aux couleurs du club vendus en lignes.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new LinkView(
                label: 'Ajouter un produit',
                url: $this->urlGenerator->generate('admin_product_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            advancedFilter: new LinkView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                size: Size::ICON,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            wiki:  $this->wikiMapper->mapToView('boutique'),
        );
    }

    private function getDropdown(Product $product, string $referer): DropdownView
    {
        if ($product->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_product_restore', [
                            'product' => $product->getId()
                        ], $referer),
                        icon: 'lucide:archive-restore',
                        variant: ColorVariant::DROPDOWN,
                    ),
                ]
            );
        }

        return new DropdownView(
            menuItems: [
                new LinkView(
                    label: 'Supprimer',
                    url: $this->urlContextService->generateUrl('admin_product_delete', ['product' => $product->getId()], $referer),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ],
        );
    }

    private function getAction(Product $entity, string $toggleStatusId): ?ListActionViewInterface
    {
        if (!$entity->isDeleted()) {
            return new ToggleStatusView(
                url: $this->urlGenerator->generate('admin_product_toggle', ['product' => $entity->getId()]),
                tokenId: $toggleStatusId,
                isActive: !$entity->isDisabled(),
            );
        }

        return null;
    }
}
