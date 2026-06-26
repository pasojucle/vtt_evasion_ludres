<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\Filter\FilterChip;

readonly class ListView
{
    /**
     * @param string $name,
     * @param string $title,
     * @param string $description,
     * @param ListItemView[] $items
     * @param ?PaginatorView $paginator
     * @param ?DropdownView $tools
     * @param ?DropdownView $settings
     * @param ?ButtonView $addItem
     * @param ?ButtonView $advancedFilter
     * @param FilterChip[] $filterChips
     * @param ?ButtonView $wiki
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public array $items,
        public ?PaginatorView $paginator = null,
        public ?DropdownView $tools = null,
        public ?DropdownView $settings = null,
        public ?ButtonView $addItem = null,
        public ?ButtonView $advancedFilter = null,
        public array $filterChips = [],
        public ?ButtonView $wiki = null,
    ) {
    }
}
