<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Enum\DropdownVariant;
use App\Dto\Enum\RoundedVariant;

readonly class DropdownView
{
    /**
     * @param string $trigger
     * @param DropdownVariant $variant
     * @param RoundedVariant $rounded
     * @param DropdownItemView[] $infoItems
     * @param ButtonView[] $menuItems
     * @param DropdownItemView[] $actionItems
     */
    public function __construct(
        public string $trigger = 'lucide:ellipsis-vertical',
        public DropdownVariant $variant = DropdownVariant::LIST_ITEM,
        public RoundedVariant $rounded = RoundedVariant::ROUNDED_NONE,
        public ?string $title = null,
        public array $infoItems = [],
        public array $menuItems = [],
        public array $actionItems = [],
    ) {
    }
}
