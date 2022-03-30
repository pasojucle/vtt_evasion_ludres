<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class ProductsViewModel
{
    public ?array $products;

    public static function fromProducts(Paginator $products, ServicesPresenter $services): ProductsViewModel
    {
        $productsViewModel = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                $productsViewModel[] = ProductViewModel::fromProduct($product, $services);
            }
        }

        $productsView = new self();
        $productsView->products = $productsViewModel;

        return $productsView;
    }
}
