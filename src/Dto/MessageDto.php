<?php

declare(strict_types=1);

namespace App\Dto;

class MessageDto
{
    public string $id;

    public string $label = '';

    public bool $isProtected = true;
}
