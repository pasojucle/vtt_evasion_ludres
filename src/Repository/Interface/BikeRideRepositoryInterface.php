<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\BikeRide;

interface BikeRideRepositoryInterface
{
    public function save(BikeRide $bikeRide, bool $flush = true): void;

    public function remove(BikeRide $bikeRide, bool $flush = true): void;
}
