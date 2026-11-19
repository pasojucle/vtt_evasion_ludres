<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\SecondHand;

interface SecondHandRepositoryInterface
{
    public function save(SecondHand $secondHand, bool $flush = true): void;

    public function remove(SecondHand $secondHand, bool $flush = true): void;
}
