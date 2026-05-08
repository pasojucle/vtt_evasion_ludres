<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\ProductState;
use App\Entity\Enum\OrderStatusEnum;

class ProductFilter extends AbstractFilter
{
    public function __construct(
        public ?ProductState $state = null,
    ) {
    }
}
