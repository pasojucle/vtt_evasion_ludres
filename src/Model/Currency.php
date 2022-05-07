<?php

declare(strict_types=1);

namespace App\Model;


class Currency
{
    private float $amount;

    public function __construct(?float $amount) {
        $this->amount = $amount;
    }

    public function toString(): string
    {
        return number_format($this->amount, 2).' €';
    }


    public function add($amountToAdd): float
    {
        return $this->amount += $amountToAdd->amount;
    }
}