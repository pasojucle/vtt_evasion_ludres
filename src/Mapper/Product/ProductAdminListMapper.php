<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Dto\View\BadgeView;
use App\Dto\View\ButtonView;
use App\Dto\View\DropdownView;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\PublishStatus;
use App\Dto\Filter\ProductFilter;
use App\Dto\View\HtmlAttributView;
use App\Dto\View\LabelView;
use App\Dto\View\ListView;
use App\Dto\View\ListItemView;
use App\Entity\Product;
use App\Mapper\FilterChipsMapper;
use App\Mapper\PaginatorMapper;
use App\Mapper\WikiMapper;
use App\Service\Filter\FilterConfigInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductAdminListMapper
{
    public function __construct(
        private PaginatorMapper $paginatorMapper,
        private FilterChipsMapper $filterChipsMapper,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private WikiMapper $wikiMapper,
    ) {
    }

    public function mapToView(Paginator $entities, string $route, int $currentPage, ProductFilter $filter, FilterConfigInterface $filterConfig): ListView
    {
        $items = [];
        /** @var Product $entity */
        foreach ($entities as $entity) {
            $state = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;

            $items[] = new ListItemView(
                labels: [
                    new LabelView($entity->getName()),
                ],
                indicators: $entity->getSizes()->map(fn ($size) => new BadgeView($size->getName()))->toArray(),
                status: new BadgeView($state->trans($this->translator), $state->variant()),
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_product", ['product' => $entity->getId()]),
                gridTemplateBadges: 'grid-cols-[1fr_70px]',
            );
        }

        return new ListView(
            id: 'product_container',
            title: 'Boutique',
            description: 'Administration des produits aux couleurs du club vendus en lignes.',
            items: $items,
            paginator: $this->paginatorMapper->mapToView($entities, $route, $currentPage, $filter),
            addItem: new ButtonView(
                label: 'Ajouter un produit',
                url: $this->urlGenerator->generate('admin_product_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            advancedFilter: new ButtonView(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => $route], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::SHEET_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            wiki:  $this->wikiMapper->mapToView('boutique'),
        );
    }

    private function getDropdown(Product $product): DropdownView
    {
        $menuItems = [];
        if ($product->isDisabled()) {
            $menuItems[] = new ButtonView(
                label: 'Activer',
                url: $this->urlGenerator->generate('admin_product_disable', ['product' => $product->getId()]),
                icon: 'lucide:toggle-left',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            );
        } else {
            $menuItems[] = new ButtonView(
                label: 'Désactiver',
                url: $this->urlGenerator->generate('admin_product_disable', ['product' => $product->getId()]),
                icon: 'lucide:toggle-right',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                    new HtmlAttributView('data-action', 'click->dropdown#close'),
                ],
            );
        }

        $menuItems[] = new ButtonView(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_product_delete', ['product' => $product->getId()]),
            icon: 'lucide:delete',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributView('data-turbo-frame', ButtonView::MODAL_CONTENT),
                new HtmlAttributView('data-action', 'click->dropdown#close'),
            ],
        );

        return new DropdownView(
            menuItems: $menuItems,
        );
    }
}
