<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class ListItemDto
{
    /**
     * @param ListCellItemDto[] $cells
     * @param ListBadgesItemDto[] $badges
     */
    public function __construct(
        public array $cells = [],
        public array $badges = [],
        public ?DropdownDto $dropdown = null,
        public ?string $background = null,
        public ?string $url = null,
        public ?ButtonDto $action = null,
    ) {

    }
}