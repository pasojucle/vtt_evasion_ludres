<?php

declare(strict_types=1);

namespace App\Dto;

use App\Model\Currency;

class SessionDto
{
    public ?int $id = null;

    public ?array $availability = null;

    public ?BikeRideDto $bikeRide;

    public ?UserDto $user;

    public ?bool $userIsOnSite = false;

    public string $userIsOnSiteToStr = '';

    public string $userIsOnSiteToHtml = '';

    public ?Currency $indemnity;

    public ?string $indemnityStr;

    public ?string $cluster = null;

    public ?array $bikeRideMemeberList = null;

    public ?string $practice = null;
}
