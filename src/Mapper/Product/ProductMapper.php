<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Core\Contract\View\ListActionViewInterface;
use App\Dto\Enum\ColorVariant;
use App\Dto\View\BadgeView;
use App\Dto\View\DropdownView;
use App\Dto\View\HtmlAttributeView;
use App\Dto\View\LabelView;
use App\Dto\View\LinkView;
use App\Dto\View\ListItemUrlView;
use App\Dto\View\ListItemView;
use App\Dto\View\ToggleStatusView;
use App\Entity\Product;
use App\Service\CsrfTokenService;
use App\Service\UrlContextService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProductMapper
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UrlContextService $urlContextService,
        private CsrfTokenService $csrfTokenService,
        private ProductStatusMapper $productStatusMapper,
    ) {
    }

    public function mapToView(Product $product, string $targetUrl): ListItemView
    {
        $tokenId = $this->csrfTokenService->getTokenId($product);
        return  new ListItemView(
            id: sprintf('product-item-%s', $product->getId()),
            labels: [
                new LabelView($product->getName()),
            ],
            indicators: $product->getSizes()->map(fn ($size) => new BadgeView($size->getName()))->toArray(),
            status: $this->productStatusMapper->mapToView($product, $tokenId),
            dropdown: $this->getDropdown($product, $targetUrl),
            isDeleted: $product->isDeleted(),
            action: $this->getAction($product, $tokenId),
            url: new ListItemUrlView(
                path: $this->urlContextService->generateUrl(
                    "admin_product",
                    ['product' => $product->getId()],
                    $targetUrl,
                ),
                htmlAttributes: [
                    new HtmlAttributeView('data-turbo-frame', LinkView::SHEET_CONTENT),
                    new HtmlAttributeView('data-action', 'click->dropdown#close')
                ],
            ),
            gridTemplateRow: 'grid-cols-[1fr_50px]',
            gridTemplateBadges: 'grid-cols-[1fr_70px]',
        );
    }

    private function getDropdown(Product $product, string $fallback): DropdownView
    {
        if ($product->isDeleted()) {
            return new DropdownView(
                menuItems: [
                    new LinkView(
                        label: 'Restaurer',
                        url: $this->urlContextService->generateUrl('admin_product_restore', [
                            'product' => $product->getId()
                        ], $fallback),
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
                    url: $this->urlContextService->generateUrl('admin_product_delete', ['product' => $product->getId()], $fallback),
                    icon: 'lucide:delete',
                    variant: ColorVariant::DROPDOWN,
                    htmlAttributes: [
                        new HtmlAttributeView('data-turbo-frame', LinkView::MODAL_CONTENT),
                        new HtmlAttributeView('data-action', 'click->dropdown#close'),
                    ],
                ),
            ],
        );
    }

    private function getAction(Product $product, string $csrfToken): ?ListActionViewInterface
    {
        if (!$product->isDeleted()) {
            return new ToggleStatusView(
                url: $this->urlGenerator->generate('admin_product_toggle', ['product' => $product->getId()]),
                csrfToken: $csrfToken,
                isActive: !$product->isDisabled(),
            );
        }

        return null;
    }
}
