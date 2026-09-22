<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Core\Contract\View\TabContentInterface;

readonly class TabView
{
    public function __construct(
        public string $title,
        public int $index,
        public bool $isActive,
        public ?TabContentInterface $view = null,
        public ?string $icon = null,
        public ?string $color = null,
    ) {
    }
}
