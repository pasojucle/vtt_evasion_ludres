<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\DropdownVariant;

readonly class DropdownDto
{
    /**
     * @param string $trigger
     * @param DropdownVariant $variant
     * @param DropdownItemDto[] $infoItems
     * @param ButtonDto[] $menuItems
     * @param DropdownItemDto[] $actionItems
     */
    public function __construct(
        public string $trigger = 'lucide:ellipsis-vertical',
        public DropdownVariant $variant = DropdownVariant::LIST_ITEM,
        public ?string $title = null,
        public array $infoItems = [],
        public array $menuItems = [],
        public array $actionItems = [],
    ) {
    }
}
