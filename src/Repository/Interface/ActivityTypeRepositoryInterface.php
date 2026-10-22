<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\BikeRideType;

interface ActivityTypeRepositoryInterface
{
    public function save(BikeRideType $bikeRideType, bool $flush = true): void;

    public function remove(BikeRideType $bikeRideType, bool $flush = true): void;
}
