<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class TabView
{
    public function __construct(
        public string $title,
        public string $icon,
        public string $content,
    ) {
    }
}
