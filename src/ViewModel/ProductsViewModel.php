<?php

namespace App\ViewModel;

use App\Service\LicenceService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;

class ProductsViewModel 
{
    public ?array $products;

    public static function fromProducts(Paginator $products, array $services): ProductsViewModel
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