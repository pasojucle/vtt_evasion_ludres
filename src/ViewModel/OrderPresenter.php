<?php

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\ViewModel\OrderViewModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrderPresenter 
{
    private ParameterBagInterface $parameterBag;
    private $viewModel;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }

    public function present(?OrderHeader $orderHeader): void
    {
        
        if (null !== $orderHeader) {
            $this->viewModel = OrderViewModel::fromOrderHeader($orderHeader);
        } else {
            $this->viewModel = new OrderViewModel();
        }
    }


    public function viewModel(): OrderViewModel
    {
        return $this->viewModel;
    }

}