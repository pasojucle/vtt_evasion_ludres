<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\UserDto;

class RegistrationProgressDto
{
    public ?int $prevStep = null;
    public ?int $nextStep = null;
    public ?int $currentIndex = null;
    public ?RegistrationStepDto $current = null;
    public array $progressBar = [];
    public ?UserDto $user = null;
    public int $season = 2021;
    public ?string $redirecToRoute = null;
}
