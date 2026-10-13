<?php

declare(strict_types=1);

namespace App\Model;

final readonly class Money
{
    public function __construct(
        private int $amountInCents,
    ) {
    }

    public function __toString(): string
    {
        return number_format($this->amountInCents / 100, 2) . ' €';
    }

    public static function fromFloat(float $amount): self
    {
        return new self((int) round($amount * 100));
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function getAmount(): float
    {
        return round($this->amountInCents / 100, 2);
    }

    public function add(self $other): self
    {
        return new self($this->amountInCents + $other->amountInCents);
    }

    public function sub(self $other): self
    {
        return new self($this->amountInCents - $other->amountInCents);
    }
}
