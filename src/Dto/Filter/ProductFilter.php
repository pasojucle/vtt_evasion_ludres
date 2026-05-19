<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\PublishStatus;
use App\Entity\Enum\OrderStatusEnum;

class ProductFilter extends AbstractFilter
{
    public function __construct(
        public ?PublishStatus $state = null,
        public ?string $partNumber = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
