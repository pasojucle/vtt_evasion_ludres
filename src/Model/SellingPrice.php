<?php

declare(strict_types=1);

namespace App\Model;

class SellingPrice
{
    public function __construct(
        public readonly float $amount,
        public readonly ?string $reason = null,
        public readonly ?float $basePrice = null,
    ) {
    }

    public function hasDiscount(): bool
    {
        return $this->basePrice !== null && $this->amount < $this->basePrice;
    }
}