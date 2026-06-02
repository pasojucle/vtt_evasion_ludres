<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Dto\Enum\RegistrationStatus;
use App\Entity\Member;

class RegistrationFilter extends AbstractFilter
{
    /**
     * Summary of __construct
     * @param RegistrationStatus $status
     * @param ?Member $member
     * @param array<int|string>|null $levels
     * @param ?int $itemsPerPage
     * @param ?string $sort
     */
    public function __construct(
        public RegistrationStatus $status = RegistrationStatus::TESTING_IN_PROGRESS,
        public ?Member $member = null,
        public ?array $levels = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = null,
    ) {
    }
}
