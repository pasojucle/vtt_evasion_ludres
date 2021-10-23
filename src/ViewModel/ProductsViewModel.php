<?php

namespace App\ViewModel;

use App\Service\LicenceService;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Security\Core\Security;

class ProductsViewModel 
{
    public ?array $products;

    public static function fromProducts(
        Paginator $products,
        string $productDirectory,
        Security $security,
        LicenceService $licenceService
    ): ProductsViewModel
    {
        $productsViewModel = [];
        if (!empty($products)) {
            foreach ($products as $product) {
                $user = $security->getUser();
                $productsViewModel[] = ProductViewModel::fromProduct($product, $productDirectory, $user, $licenceService);
            }
        }

        $productsView = new self();
        $productsView->products = $productsViewModel;

        return $productsView;
    }
}