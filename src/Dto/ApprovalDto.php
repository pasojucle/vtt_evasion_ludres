<?php

declare(strict_types=1);

namespace App\Dto;

class ApprovalDto
{
    public string $name = '';

    public bool $value = false;

    public string $toString = "";

    public array $toHtml = [];
}