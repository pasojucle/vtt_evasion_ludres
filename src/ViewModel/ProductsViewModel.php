<?php

namespace App\ViewModel;

use App\Entity\Product;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductsViewModel 
{
    public ?array $products;

    public static function fromProduct(Paginator $products, string $productDirectory): ProductsViewModel
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