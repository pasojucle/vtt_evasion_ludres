<?php

namespace App\ViewModel;


use Doctrine\ORM\Tools\Pagination\Paginator;

class ProductsViewModel 
{
    public ?array $products;

    public static function fromProducts(Paginator $products, string $productDirectory): ProductsViewModel
    {
        $productsViewModel = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                $productsViewModel[] = ProductViewModel::fromProduct($product, $productDirectory);
            }
        }

        $productsView = new self();
        $productsView->products = $productsViewModel;

        return $productsView;
    }
}