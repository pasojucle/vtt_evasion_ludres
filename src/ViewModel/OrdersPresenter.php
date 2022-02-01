<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class OrdersPresenter extends AbstractPresenter
{
    public function present(Paginator $ordrers): void
    {
        if (!empty($ordrers)) {
            $this->viewModel = OrdersViewModel::fromOrders($ordrers, $this->services);
        } else {
            $this->viewModel = new OrdersViewModel();
        }
    }

    public function viewModel(): OrdersViewModel
    {
        return $this->viewModel;
    }
}
