<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\PublishStatus;

class ProductFilter extends AbstractFilter
{
    public function __construct(
        public ?PublishStatus $state = null,
        public ?string $partNumber = null,
        public ?bool $showDeleted = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
        public ?int $page = null,
    ) {
    }
}
