<?php

declare(strict_types=1);

namespace App\ViewModel\BikeRideType;

use App\ViewModel\AbstractPresenter;
use App\ViewModel\BikeRideType\BikeRideTypesViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BikeRideTypesPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Paginator $bikeRideTypes): void
    {
        if (!empty($bikeRideTypes)) {
            $this->viewModel = BikeRideTypesViewModel::fromBikeRideTypes($bikeRideTypes);
        } else {
            $this->viewModel = new BikeRideTypesViewModel();
        }
    }

    public function viewModel(): BikeRideTypesViewModel
    {
        return $this->viewModel;
    }
}
