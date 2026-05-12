<?php

declare(strict_types=1);

namespace App\Dto\Filter;

class ActivityAdvancedFilter extends AbstractFilter
{
    public function __construct(
        public ?string $sort = null,
    ) {
    }
}
