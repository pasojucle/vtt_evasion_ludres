<?php

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class ProductsPresenter extends AbstractPresenter
{
    public function present(Paginator $products): void
    {
        if (!empty($products)) {
            $this->viewModel = ProductsViewModel::fromProducts($products, $this->data);
        } else {
            $this->viewModel = new ProductsViewModel();
        }
    }


    public function viewModel(): ProductsViewModel
    {
        return $this->viewModel;
    }

}