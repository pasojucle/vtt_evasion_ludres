<?php

declare(strict_types=1);

namespace App\Dto;

class LinkDto
{
    public ?string $image;

    public string $description;

    public ?string $content;

    public array $btnShow;

    public string $url;

    public bool $novelty;
}
