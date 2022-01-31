<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;

class OrderPresenter extends AbstractPresenter
{
    public function present(?OrderHeader $orderHeader): void
    {
        if (null !== $orderHeader) {
            $this->viewModel = OrderViewModel::fromOrderHeader($orderHeader, $this->services);
        } else {
            $this->viewModel = new OrderViewModel();
        }
    }

    public function viewModel(): OrderViewModel
    {
        return $this->viewModel;
    }
}
