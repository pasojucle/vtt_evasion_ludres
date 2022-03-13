<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\BikeRide;

class BikeRidePresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?BikeRide $bikeRide): void
    {
        if (null !== $bikeRide) {
            $this->viewModel = BikeRideViewModel::fromBikeRide($bikeRide, $this->services);
        } else {
            $this->viewModel = new BikeRideViewModel();
        }
    }

    public function viewModel(): BikeRideViewModel
    {
        return $this->viewModel;
    }
}
