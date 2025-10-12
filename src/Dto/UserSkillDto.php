<?php

declare(strict_types=1);

namespace App\Dto;

class UserSkillDto
{
    public ?array $evaluation = null;

    public ?string $evaluateAt = null;

    public ?string $content = null;

    public ?array $category = null;

    public ?array $level = null;
}
