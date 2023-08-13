<?php

declare(strict_types=1);

namespace App\Dto;

class BackgroundDto
{
    public ?int $id = null;
    
    public ?string $filename;

    public ?string $path;
}