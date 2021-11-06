<?php

namespace App\ViewModel;

use App\Service\LicenceService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;

class ProductsViewModel 
{
    public ?array $products;

    public static function fromProducts(Paginator $products, array $data): ProductsViewModel
    {
        $productsViewModel = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                $productsViewModel[] = ProductViewModel::fromProduct($product, $data);
            }
        }

        $productsView = new self();
        $productsView->products = $productsViewModel;

        return $productsView;
    }
}