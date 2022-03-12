<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class ProductsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(Paginator $products): void
    {
        if (!empty($products)) {
            $this->viewModel = ProductsViewModel::fromProducts($products, $this->services);
        } else {
            $this->viewModel = new ProductsViewModel();
        }
    }

    public function viewModel(): ProductsViewModel
    {
        return $this->viewModel;
    }
}
