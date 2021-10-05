<?php

namespace App\ViewModel;

use App\Entity\Product;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductsPresenter 
{
    private ParameterBagInterface $parameterBag;
    private $viewModel;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function present(Paginator $products): void
    {
        $productDirectory = $this->parameterBag->get('products_directory');

        if (!empty($products)) {
            $this->viewModel = ProductsViewModel::fromProducts($products, $productDirectory);
        } else {
            $this->viewModel = new ProductsViewModel();
        }
    }


    public function viewModel(): ProductsViewModel
    {
        return $this->viewModel;
    }

}