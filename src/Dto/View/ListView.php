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
     * @param ?LinkView $addItem
     * @param ?LinkView $advancedFilter
     * @param FilterChip[] $filterChips
     * @param ?LinkView $wiki
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public array $items,
        public ?PaginatorView $paginator = null,
        public ?DropdownView $tools = null,
        public ?DropdownView $settings = null,
        public ?LinkView $addItem = null,
        public ?LinkView $advancedFilter = null,
        public array $filterChips = [],
        public ?LinkView $wiki = null,
    ) {
    }
}
