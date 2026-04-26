<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\OrderStatusEnum;

class OrderFilter extends AbstractFilter
{
    public function __construct(
        public ?OrderStatusEnum $status = null,
    )
    {

    }
}