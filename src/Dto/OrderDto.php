<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Enum\OrderStatusEnum;

class OrderDto
{
    public ?int $id;

    public ?UserDto $user;

    public array $orderLines;

    public OrderStatusEnum $status;

    public ?string $statusToString = '';

    public ?string $amount;

    public string $createdAt;

    public ?string $comments = null;
}
