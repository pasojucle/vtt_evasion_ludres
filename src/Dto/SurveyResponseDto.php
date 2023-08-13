<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\UserDto;


class SurveyResponseDto
{

    public ?UserDto $user;

    public ?string $issue;

    public ?string $value;

    public ?string $uuid;
}