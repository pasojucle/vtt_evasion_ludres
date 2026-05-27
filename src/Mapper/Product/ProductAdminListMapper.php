<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Dto\BadgeDto;
use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\PublishStatus;
use App\Dto\Filter\ProductFilter;
use App\Dto\HtmlAttributDto;
use App\Dto\LabelDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
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

    public function mapToView(Paginator $entities, string $route, int $currentPage, ProductFilter $filter, FilterConfigInterface $filterConfig): ListDto
    {
        $items = [];
        /** @var Product $entity */
        foreach ($entities as $entity) {
            $state = $entity->isDisabled() ? PublishStatus::DISABLED : PublishStatus::ENABLED;

            $items[] = new ListItemDto(
                labels: [
                    new LabelDto($entity->getName()),
                ],
                indicators: $entity->getSizes()->map(fn ($size) => new BadgeDto($size->getName()))->toArray(),
                status: new BadgeDto($state->trans($this->translator), $state->variant()),
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_product", ['product' => $entity->getId()]),
            );
        }

        return new ListDto(
            items: $items,
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            addItem: new ButtonDto(
                label: 'Ajouter un produit',
                url: $this->urlGenerator->generate('admin_product_add'),
                icon: 'lucide:plus',
                variant: ColorVariant::DEFAULT,
            ),
            advancedFilter: new ButtonDto(
                url: $this->urlGenerator->generate('admin_fiter_advanced', array_merge(['route' => 'admin_products'], $filter->toQueryParams())),
                icon: 'lucide:settings-2',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::SHEET_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close')
                ],
            ),
            filterChips: $this->filterChipsMapper->mapToView($filter, $filterConfig),
            wiki:  $this->wikiMapper->mapToView('boutique'),
        );
    }

    private function getDropdown(Product $product): DropdownDto
    {
        $menuItems = [];
        if ($product->isDisabled()) {
            $menuItems[] = new ButtonDto(
                label: 'Activer',
                url: $this->urlGenerator->generate('admin_product_disable', ['product' => $product->getId()]),
                icon: 'lucide:toggle-left',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close'),
                ],
            );
        } else {
            $menuItems[] = new ButtonDto(
                label: 'Désactiver',
                url: $this->urlGenerator->generate('admin_product_disable', ['product' => $product->getId()]),
                icon: 'lucide:toggle-right',
                variant: ColorVariant::DROPDOWN,
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                    new HtmlAttributDto('data-action', 'click->dropdown#close'),
                ],
            );
        }

        $menuItems[] = new ButtonDto(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_product_delete', ['product' => $product->getId()]),
            icon: 'lucide:delete',
            variant: ColorVariant::DROPDOWN,
            htmlAttributes: [
                new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                new HtmlAttributDto('data-action', 'click->dropdown#close'),
            ],
        );

        return new DropdownDto(
            menuItems: $menuItems,
        );
    }
}
