<?php

declare(strict_types=1);

namespace App\ViewModel\BikeRideType;

use App\ViewModel\BikeRideType\BikeRideTypeViewModel;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BikeRideTypesViewModel
{
    public ?array $bikeRideTypes = [];

    public static function fromBikeRideTypes(array|Paginator|Collection $bikeRideTypes): BikeRideTypesViewModel
    {
        $bikeRidesViewModel = [];
        if (!empty($bikeRideTypes)) {
            foreach ($bikeRideTypes as $bikeRideType) {
                $bikeRidesViewModel[] = BikeRideTypeViewModel::fromBikeRideType($bikeRideType);
            }
        }

        $bikeRidesView = new self();
        $bikeRidesView->bikeRideTypes = $bikeRidesViewModel;

        return $bikeRidesView;
    }
}
