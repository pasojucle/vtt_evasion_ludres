<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\BikeRideType;
use App\Entity\Level;

class IndemnityDto
{
    public ?array $services;

    public ?int $id = null;

    public ?Level $level = null;

    public ?BikeRideType $bikeRideType = null;

    public ?string $amount = null;
}
