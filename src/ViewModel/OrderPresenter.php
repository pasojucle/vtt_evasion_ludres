<?php

namespace App\ViewModel;

use App\Entity\User;
use App\Entity\OrderHeader;
use App\Service\LicenceService;
use App\ViewModel\OrderViewModel;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrderPresenter 
{
    private LicenceService $licenceService;
    private ParameterBagInterface $parameterBag;
    private User $user;
    private $viewModel;

    public function __construct(LicenceService $licenceService, ParameterBagInterface $parameterBag)
    {
        $this->licenceService = $licenceService;
        $this->parameterBag = $parameterBag;
    }

    public function present(?OrderHeader $orderHeader): void
    {
        $productDirectory = $this->parameterBag->get('products_directory');

        if (null !== $orderHeader) {
            $this->viewModel = OrderViewModel::fromOrderHeader($orderHeader, $productDirectory, $this->licenceService, $this->licenceService);
        } else {
            $this->viewModel = new OrderViewModel();
        }
    }


    public function viewModel(): OrderViewModel
    {
        return $this->viewModel;
    }

}