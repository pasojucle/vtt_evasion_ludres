<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class OrdersPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(Paginator $orders): void
    {
        if (null !== $orders->count()) {
            $this->viewModel = OrdersViewModel::fromOrders($orders, $this->services);
        } else {
            $this->viewModel = new OrdersViewModel();
        }
    }

    public function viewModel(): OrdersViewModel
    {
        return $this->viewModel;
    }
}
