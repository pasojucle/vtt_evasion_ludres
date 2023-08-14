<?php

declare(strict_types=1);

namespace App\Dto;

use App\Model\Currency;

class SessionDto
{
    public ?int $id;

    public ?array $availability;

    public ?BikeRideDto $bikeRide;

    public ?UserDto $user;

    public ?bool $userIsOnSite;

    public ?Currency $indemnity;

    public ?string $indemnityStr;

    public ?string $cluster = null;

    public ?array $bikeRideMemeberList = null;
}
