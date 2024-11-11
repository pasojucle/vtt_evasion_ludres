<?php

declare(strict_types=1);

namespace App\Dto;

class UserSkillDto
{
    public ?array $skill = null;

    public ?array $evaluation = null;

    public ?string $evaluateAt = null;
}
