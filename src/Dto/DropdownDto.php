<?php

declare(strict_types=1);

namespace App\Dto;


class DropdownDto
{
    public string $trigger = 'lucide:ellipsis-vertical';

    public string $position = 'absolute h-full w-8 right-0 top-0';

    public ?string $title = null;

    /** @var DropdownItemDto[] */
    public array $infoItems = [];

    /** @var ButtonDto[] */
    public array $menuItems = [];

    /** @var DropdownItemDto[] */
    public array $actionItems = [];

    public function addMenuItem(string $label, string $icon, string $url, string $target = ButtonDto::TOP): void
    {
        $this->menuItems[] = new ButtonDto($label, $url, $target, $icon);
    }

    public function addInfoItem(string $label, string $icon): void
    {
        $this->infoItems[] = new DropdownItemDto($label, $icon);
    }

    public function addActionItem(string $label, string $icon, array $data): void
    {
        $this->actionItems[] = new DropdownItemDto($label, $icon, $data);
    }
}