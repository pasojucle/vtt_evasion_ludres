<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DropdownDto
{
    public string $trigger = 'lucide:ellipsis-vertical';

    public string $position = 'absolute h-full w-8 right-0 top-0';

    public ?string $title = null;

    /** @var DropdownItemDto[] */
    public array $infoItems = [];

    /** @var ButtonDto[] */
    private array $menuItems = [];

    /** @var ButtonDto[] */
    private array $menuItemsFromSection = [];

    /** @var DropdownItemDto[] */
    public array $actionItems = [];

    private ?UrlGeneratorInterface $urlGenerator = null;

    public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): self
    {
        $this->urlGenerator = $urlGenerator;
        return $this;
    }

    public function addMenuItem(string $label, RouteDto $route, string $icon = 'lucide:settings-2', string $target = ButtonDto::TOP): void
    {
        $this->menuItems[] = new ButtonDto($label, $this->getUrl($route), $target, $icon);
    }

    public function addSectionItem(string $label, RouteDto $route, string $icon, string $target = ButtonDto::TOP): void
    {
        $this->menuItemsFromSection[] = new ButtonDto($label, $this->getUrl($route), $target, $icon);
    }

    public function addInfoItem(string $label, string $icon): void
    {
        $this->infoItems[] = new DropdownItemDto($label, $icon);
    }

    public function addActionItem(string $label, string $icon, array $data): void
    {
        $this->actionItems[] = new DropdownItemDto($label, $icon, $data);
    }

    public function getMenuItems(): array
    {
        return array_merge($this->menuItems, $this->menuItemsFromSection);
    }

    private function getUrl(RouteDto $route): string
    {
        return  $this->urlGenerator
            ? $this->urlGenerator->generate($route->name, $route->params)
            : $route;
    }
}
