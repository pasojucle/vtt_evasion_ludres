<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\OrderHeader;
use Symfony\Component\Form\FormInterface;

class OrderPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?OrderHeader $orderHeader, ?FormInterface $form = null): void
    {
        if (null !== $orderHeader) {
            $this->viewModel = OrderViewModel::fromOrderHeader($orderHeader, $this->services, $form);
        } else {
            $this->viewModel = new OrderViewModel();
        }
    }

    public function viewModel(): OrderViewModel
    {
        return $this->viewModel;
    }
}
