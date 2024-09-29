<?php

declare(strict_types=1);

namespace App\Dto;

class SkillDto
{
    public ?int $id = null;

    public string $content = '';

    public array $category = [];

    public array $level = [];
}
