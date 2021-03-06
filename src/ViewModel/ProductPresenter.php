<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Product;

class ProductPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Product $product): void
    {
        if (null !== $product) {
            $this->viewModel = ProductViewModel::fromProduct($product, $this->services);
        } else {
            $this->viewModel = new ProductViewModel();
        }
    }

    public function viewModel(): ProductViewModel
    {
        return $this->viewModel;
    }
}
