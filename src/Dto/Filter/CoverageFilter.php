<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Member;

class CoverageFilter extends AbstractFilter
{
    /**
     * @param ?Member $member
     * @param array<int|string>|null $levels
     * @param ?int $itemsPerPage
     * @param ?string $sort
     */
    public function __construct(
        public ?Member $member = null,
        public ?array $levels = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = 'ASC',
    ) {
    }
}
