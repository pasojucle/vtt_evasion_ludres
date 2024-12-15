<?php

declare(strict_types=1);

namespace App\Dto;

class SecondHandDto
{
    public ?int $id = null;

    public string $name = "";

    public ?UserDto $user = null;

    public ?string $price = null;

    public array $images = [];

    public string $pathName = '';

    public string $content = '';

    public string $category = '';

    public string $createdAt = '';

    public bool $valid = false;

    public bool $disabled = false;

    public string $status = '';

    public bool $novelty = false;
}
