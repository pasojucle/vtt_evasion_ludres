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

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
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
