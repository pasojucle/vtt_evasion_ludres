<?php

declare(strict_types=1);

namespace App\Dto\Filter;

use App\Entity\Enum\PermissionEnum;
use App\Entity\Member;
use App\Service\SeasonService;

class UserFilter extends AbstractFilter
{
    /**
     * Summary of __construct
     * @param ?Member $member
     * @param ?int $season
     * @param array<int|string>|null $levels
     * @param bool $isBoardMember
     * @param ?PermissionEnum[] $permissions
     * @param ?int $itemsPerPage
     * @param ?string $sort
     */
    public function __construct(
        public ?Member $member = null,
        public ?int $season = null,
        public ?array $levels = null,
        public ?bool $isBoardMember = null,
        public ?array $permissions = null,
        public ?int $itemsPerPage = null,
        public ?string $sort = 'ASC',
        public ?int $page = null,
    ) {
    }

    public function setDefaultSeason(SeasonService $seasonService): void
    {
        if (null === $this->season) {
            $this->season = $seasonService->getCurrentSeason();
        }
    }
}
