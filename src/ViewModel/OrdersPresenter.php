<?php

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\Service\LicenceService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrdersPresenter extends AbstractPresenter
{
    public function present(Paginator $ordrers): void
    {
        if (!empty($ordrers)) {
            $this->viewModel = OrdersViewModel::fromOrders($ordrers, $this->data);
        } else {
            $this->viewModel = new OrdersViewModel();
        }
    }


    public function viewModel(): OrdersViewModel
    {
        return $this->viewModel;
    }

}