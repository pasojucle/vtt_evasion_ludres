<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\BikeRideType;
use App\Entity\Indemnity;
use App\Entity\Level;

class IndemnityDto
{
    public ?array $services;

    public ?Indemnity $entity;

    public ?Level $level;

    public ?BikeRideType $bikeRideType;

    public ?string $amount;
}