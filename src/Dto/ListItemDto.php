<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class ListItemDto
{
    /**
     * @param ListCellItemDto[] $cells
     */
    public function __construct(
        public array $cells = [],
        public ?DropdownDto $dropdown = null,
        public ?string $background = null,
        public ?string $url = null,
        public ?ButtonDto $action = null,
    ) {

    }
}