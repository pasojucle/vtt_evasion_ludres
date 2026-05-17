<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\OrderStatusEnum;
use App\Entity\Member;

class OrderFilter extends AbstractFilter
{
    public function __construct(
        public ?OrderStatusEnum $status = null,
        public ?Member $member = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
