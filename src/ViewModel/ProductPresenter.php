<?php

namespace App\ViewModel;

use App\Entity\Product;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProductPresenter 
{
    private ParameterBagInterface $parameterBag;
    private $viewModel;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function present(?Product $product): void
    {
        $productDirectory = $this->parameterBag->get('products_directory');
        
        if (null !== $product) {
            $this->viewModel = ProductViewModel::fromProduct($product, $productDirectory);
        } else {
            $this->viewModel = new ProductViewModel();
        }
    }


    public function viewModel(): ProductViewModel
    {
        return $this->viewModel;
    }

}