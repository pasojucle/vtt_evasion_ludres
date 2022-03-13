<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class BikeRidesViewModel
{
    public ?array $bikeRides = [];

    public static function fromBikeRides(array|Paginator $bikeRides, array $services): BikeRidesViewModel
    {
        $bikeRidesViewModel = [];
        if (!empty($bikeRides)) {
            foreach ($bikeRides as $bikeRide) {
                $bikeRidesViewModel[] = BikeRideViewModel::fromBikeRide($bikeRide, $services);
            }
        }

        $bikeRidesView = new self();
        $bikeRidesView->bikeRides = $bikeRidesViewModel;

        return $bikeRidesView;
    }
}
