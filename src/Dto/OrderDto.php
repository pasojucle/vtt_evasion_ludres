<?php

declare(strict_types=1);

namespace App\Dto;

class OrderDto
{
    public ?int $id;

    public ?UserDto $user;

    public array $orderLines;

    public ?int $status;

    public ?string $statusToString = '';

    public ?string $amount;

    public string $createdAt;
}
