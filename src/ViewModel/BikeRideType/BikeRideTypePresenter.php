<?php

declare(strict_types=1);

namespace App\ViewModel\BikeRideType;

use App\Entity\BikeRideType;
use App\ViewModel\AbstractPresenter;
use App\ViewModel\BikeRideType\BikeRideTypeViewModel;

class BikeRideTypePresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?BikeRideType $bikeRideType): void
    {
        if (null !== $bikeRideType) {
            $this->viewModel = BikeRideTypeViewModel::fromBikeRideType($bikeRideType);
        } else {
            $this->viewModel = new BikeRideTypeViewModel();
        }
    }

    public function viewModel(): BikeRideTypeViewModel
    {
        return $this->viewModel;
    }
}
