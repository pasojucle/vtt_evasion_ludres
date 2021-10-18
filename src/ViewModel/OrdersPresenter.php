<?php

namespace App\ViewModel;

use App\Entity\OrderHeader;
use App\Service\LicenceService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OrdersPresenter 
{
    private LicenceService $licenceService;
    private ParameterBagInterface $parameterBag;
    private $viewModel;

    public function __construct(LicenceService $licenceService, ParameterBagInterface $parameterBag)
    {
        $this->licenceService = $licenceService;
        $this->parameterBag = $parameterBag;
    }

    public function present(Paginator $ordrers): void
    {
        $productDirectory = $this->parameterBag->get('products_directory');
        $currentSeason = $this->licenceService->getCurrentSeason();
        if (!empty($ordrers)) {
            $this->viewModel = OrdersViewModel::fromOrders($ordrers, $productDirectory, $currentSeason);
        } else {
            $this->viewModel = new OrdersViewModel();
        }
    }


    public function viewModel(): OrdersViewModel
    {
        return $this->viewModel;
    }

}