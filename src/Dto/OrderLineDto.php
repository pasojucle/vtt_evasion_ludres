<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Enum\OrderLineStateEnum;

class OrderLineDto
{
    public ?int $id;

    public ?ProductDto $product = null;

    public int $quantity = 0;

    public string $size = '';

    public float $amount = 0;

    public string $amountToString = '';

    public ?string $formName;

    public OrderLineStateEnum $state = OrderLineStateEnum::IN_STOCK;
}
