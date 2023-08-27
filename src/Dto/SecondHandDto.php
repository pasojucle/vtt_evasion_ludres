<?php

declare(strict_types=1);

namespace App\Dto;

use App\Model\Currency;

class SecondHandDto
{
    public ?int $id = null;

    public string $name = "";

    public ?UserDto $user = null;

    public ?string $price = null;

    public string $filename = '';

    public string $pathName = '';

    public string $content = '';

    public string $category = '';

    public string $createdAt = '';

    public bool $valid = false;

    public string $validToString = '';
}
