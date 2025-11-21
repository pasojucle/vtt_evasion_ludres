<?php

declare(strict_types=1);

namespace App\Dto;

class LicenceAuthorizationDto
{
    
    public ?int $id = null;
    
    public string $title = '';

    public ?bool $value = false;

    public string $toString = "";

    public array $toHtml = [];

    public string $content = '';
}
