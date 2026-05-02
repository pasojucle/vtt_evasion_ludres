<?php

declare(strict_types=1);

namespace App\Mapper\Product;

use App\Dto\ButtonDto;
use App\Dto\DropdownDto;
use App\Dto\Enum\ColorVariant;
use App\Dto\Enum\ProductStateEnum;
use App\Dto\HtmlAttributDto;
use App\Dto\ListBadgesItemDto;
use App\Dto\ListCellItemDto;
use App\Dto\ListDto;
use App\Dto\ListItemDto;
use App\Entity\Product;
use App\Mapper\PaginatorMapper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductAdminListMapper
{
    public function __construct(
        private PaginatorMapper $paginatorMapper,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
    )
    {

    }

    public function mapToView(Paginator $entities, string $route, int $currentPage,  $filter): ListDto
    {
        $items = [];
        /** @var Product $entity */
        foreach ($entities as $entity) {
            $state = $entity->isDisabled() ? ProductStateEnum::DISABLED : ProductStateEnum::ENABLED;

            $items[] = new ListItemDto(
                cells: [
                    new ListCellItemDto($entity->getName()),
                    new ListCellItemDto($state->trans($this->translator), ListCellItemDto::TYPE_BADGE, $state->variant()),
                ],
                badges: $entity->getSizes()->map(fn ($size) => new ListBadgesItemDto($size->getName()))->toArray(),
                dropdown: $this->getDropdown($entity),
                url: $this->urlGenerator->generate("admin_product", ['product' => $entity->getId()]),
            );
        }

        return new ListDto(
            items: $items,
            paginator: $this->paginatorMapper->fromEntities($entities, $route, $currentPage, $filter),
            addItem: new ButtonDto(
                'Ajouter un produit',
                $this->urlGenerator->generate('admin_product_add'),
                'lucide:plus',
                ColorVariant::DEFAULT,
            )
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
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
        } else {
            $menuItems[] = new ButtonDto(
                label: 'Désactiver',
                url: $this->urlGenerator->generate('admin_product_disable', ['product' => $product->getId()]),
                icon: 'lucide:toggle-right',
                htmlAttributes: [
                    new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
                ],
            );
        }

        $menuItems[] = new ButtonDto(
            label: 'Supprimer',
            url: $this->urlGenerator->generate('admin_product_delete', ['product' => $product->getId()]),
            icon: 'lucide:delete',
            htmlAttributes: [
                new HtmlAttributDto('data-turbo-frame', ButtonDto::MODAL_CONTENT),
            ],
        );

        return new DropdownDto(
            menuItems: $menuItems, 
        );
    }
 }