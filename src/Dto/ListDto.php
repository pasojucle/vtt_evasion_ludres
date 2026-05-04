<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class ListDto
{
    /**
     * @param ListItemDto[] $items
     * @param ?PaginatorDto $paginator
     * @param ?DropdownDto $tools
     * @param ?DropdownDto $settings
     * @param ?ButtonDto $addItem
     * @param ?ButtonDto $wiki
     */
    public function __construct(
        public array $items, 
        public ?PaginatorDto $paginator,
        public ?DropdownDto $tools = null,
        public ?DropdownDto $settings = null,
        public ?ButtonDto $addItem = null,
        public ?ButtonDto $wiki = null,
    ) 
    {

    }
}
