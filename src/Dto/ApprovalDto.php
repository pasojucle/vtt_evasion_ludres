<?php

declare(strict_types=1);

namespace App\Dto;

class ApprovalDto
{
    public int $id;
    
    public string $name = '';
    
    public string $fullName = '';

    public ?bool $value = false;

    public string $toString = "";

    public array $toHtml = [];
}
