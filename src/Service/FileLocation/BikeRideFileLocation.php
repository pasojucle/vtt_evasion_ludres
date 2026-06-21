<?php

declare(strict_types=1);

namespace App\Service\FileLocation;

use App\Entity\BikeRide;
use App\Entity\BikeRideTrack;

class BikeRideFileLocation extends AbstractFileLocation
{
    public function supports(string $className): bool
    {
        return in_array($className, [BikeRide::class, BikeRideTrack::class]);
    }

    public function getBaseDirectoryName(): string
    {
        return 'bike_ride';
    }
}
