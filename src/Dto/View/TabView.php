<?php

declare(strict_types=1);

namespace App\Dto\View;

use App\Dto\View\Interface\TabContentInterface;

readonly class TabView
{
    public function __construct(
        public string $title,
        public ?TabContentInterface $view = null,
        public ?string $icon = null,
        public ?string $color = null,
    ) {
    }
}
