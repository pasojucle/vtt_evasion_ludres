<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class ListDto
{
    /**
     * @param ListItemDto[] $items
     */
    public function __construct(
        public array $items, 
        public ?PaginatorDto $paginator,
        public ?DropdownDto $tools = null,
        public ?DropdownDto $settings = null,
        public ?ButtonDto $addItem = null,
    ) 
    {

    }

    // public function addItem(ListItemDto $item): void
    // {
    //     $this->items[] = $item;
    // }

    // public function getItems(): array
    // {
    //     return $this->items;
    // }

    // public function setAddItem(string $label, string $route): void
    // {
    //     $this->addItem = new ButtonDto(
    //         $label,
    //         $route,
    //         ButtonDto::TOP,
    //         'lucide:plus',
    //         ColorVariant::DEFAULT,
    //         $label
    //     );
    // }

    //     public function getAddItem(): ?ButtonDto
    // {
    //     return $this->addItem;
    // }
}
