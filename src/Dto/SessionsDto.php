<?php

declare(strict_types=1);

namespace App\Dto;


use App\Entity\Session;
use App\Model\Currency;

class SessionsDto
{
    public array $sessions = [];

    public array $bikeRideMembers = [];
}