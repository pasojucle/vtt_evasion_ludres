<?php

declare(strict_types=1);

namespace App\Dto;

class DropdownDto
{
    public ?string $title = null;

    /** @var DropdownItemDto[] */
    public array $infoItems = [];

    /** @var DropdownItemDto[] */
    public array $menuItems = [];

    public function addMenuItem(string $label, string $icon, string $url, string $target = ButtonDto::TOP): void
    {
        $this->menuItems[] = new ButtonDto($label, $icon, $url, $target);
    }

    public function addInfoItem(string $label, string $icon): void
    {
        $this->infoItems[] = new DropdownItemDto($label, $icon);
    }
}