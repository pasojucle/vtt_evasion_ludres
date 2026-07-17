<?php

declare(strict_types=1);

namespace App\Dto\Filter;

class BikeRideTypeFilter extends AbstractFilter
{
    public function __construct(
        public ?string $name = null,
        public ?bool $showDeleted = null,
        public ?string $sort = null,
        public ?int $page = null,
    ) {
    }
}
