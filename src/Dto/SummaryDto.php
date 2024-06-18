<?php

declare(strict_types=1);

namespace App\Dto;

class SummaryDto
{
    public ?int $id;

    public string $createdAt = '';

    public string $title = '';

    public string $content = '';

    public bool $novelty = false;
}
