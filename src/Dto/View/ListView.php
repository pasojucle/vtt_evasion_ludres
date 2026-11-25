<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\FilterChipView;

readonly class ListView
{
    /**
     * @param string $name,
     * @param string $title,
     * @param string $description,
     * @param ListItemView[] $items
     * @param ?PaginatorView $paginator
     * @param ?DropdownView $dropdown
     * @param ?LinkView $addItem
     * @param ?LinkView $advancedFilter
     * @param FilterChipView[] $filterChipViews
     */
    public function __construct(
        public string $name,
        public string $title,
        public string $description,
        public array $items,
        public ?PaginatorView $paginator = null,
        public ?DropdownView $dropdown = null,
        public ?LinkView $addItem = null,
        public ?LinkView $advancedFilter = null,
        public array $filterChipViews = [],
        public ?LinkView $fallback = null,
        public ?string $sortableUrl = null,
    ) {
    }
}
