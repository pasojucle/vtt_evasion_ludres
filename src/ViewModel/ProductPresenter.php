<?php

namespace App\ViewModel;

use App\Entity\Product;

class ProductPresenter extends AbstractPresenter
{
    public function present(?Product $product): void
    {
        if (null !== $product) {
            $this->viewModel = ProductViewModel::fromProduct($product, $this->data);
        } else {
            $this->viewModel = new ProductViewModel();
        }
    }


    public function viewModel(): ProductViewModel
    {
        return $this->viewModel;
    }

}