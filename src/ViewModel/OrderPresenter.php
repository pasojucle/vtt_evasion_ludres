<?php

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\ViewModel\OrderViewModel;

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