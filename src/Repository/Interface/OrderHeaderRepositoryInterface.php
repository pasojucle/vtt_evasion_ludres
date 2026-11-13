<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Entity\OrderHeader;

interface OrderHeaderRepositoryInterface
{
    public function save(OrderHeader $orderHeader, bool $flush = true): void;

    public function remove(OrderHeader $orderHeader, bool $flush = true): void;
}
