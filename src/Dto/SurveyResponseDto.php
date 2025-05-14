<?php

declare(strict_types=1);

namespace App\Dto;

class SurveyResponseDto
{
    public array $user;

    public ?string $issue;

    public ?string $value;

    public ?string $uuid;
}
