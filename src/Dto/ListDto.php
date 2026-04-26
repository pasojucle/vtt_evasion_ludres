<?php

declare(strict_types=1);

namespace App\Dto;


class ListDto
{
    /** @var ListItemDto[] */
    public array $items = [];

    public ?DropdownDto $tools = null;
    public ?DropdownDto $settings = null;
    public ?PaginatorDto $paginator = null;

    public function addItem(ListItemDto $item): void
    {
        $this->items[] = $item;
    }
}
