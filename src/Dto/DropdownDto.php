<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Enum\ColorVariant;


readonly class DropdownDto
{
    /**
     * @param string $trigger
     * @param string $position
     * @param DropdownItemDto[] $infoItems
     * @param ButtonDto[] $menuItems
     * @param DropdownItemDto[] $actionItems
     */
    public function __construct(
        public string $trigger = 'lucide:ellipsis-vertical',
        public string $position = 'absolute h-full w-8 right-0 top-0',
        public ?string $title = null,
        public array $infoItems = [],
        public array $menuItems = [],
        public array $actionItems = [],
    ) 
    {

    }


    // public function addMenuItem(string $label, string $route, string $icon = 'lucide:settings-2', string $turboFrame = ButtonDto::TOP): void
    // {
    //     $button = new ButtonDto($label, $route, $turboFrame, $icon, ColorVariant::DROPDOWN);
    //     $button->addHtmlAttribut('data-action', 'click->dropdown#close');

    //     $this->menuItems[] = $button;
    // }

    // public function addSectionItem(string $label, string $route, string $icon, string $turboFrame = ButtonDto::TOP): void
    // {
    //     $button = new ButtonDto($label, $route, $turboFrame, $icon, ColorVariant::DROPDOWN);
    //     $button->addHtmlAttribut('data-action', 'click->dropdown#close');

    //     $this->menuItemsFromSection[] = $button;
    // }

    // public function addInfoItem(string $label, string $icon): void
    // {
    //     $this->infoItems[] = new DropdownItemDto($label, $icon);
    // }

    // public function addActionItem(DropdownItemDto $actionItem): void
    // {
    //     $this->actionItems[] = $actionItem;
    // }

    // public function getMenuItems(): array
    // {
    //     return array_merge($this->menuItems, $this->menuItemsFromSection);
    // }
}
