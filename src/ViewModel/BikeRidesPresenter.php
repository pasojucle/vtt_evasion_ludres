<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class BikeRidesPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Paginator $bikeRides): void
    {
        if (!empty($bikeRides)) {
            $this->viewModel = BikeRidesViewModel::fromBikeRides($bikeRides, $this->services);
        } else {
            $this->viewModel = new BikeRidesViewModel();
        }
    }

    public function viewModel(): BikeRidesViewModel
    {
        return $this->viewModel;
    }
}
