<?php

declare(strict_types=1);

namespace App\Dto;

class MessageDto
{
    public int $id;

    public string $label = '';

    public bool $isProtected = true;
}
