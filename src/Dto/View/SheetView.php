<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class SheetView
{
    public function __construct(
        public string $title,
        public string $description,
        public string $action,
    ) {
    }
}
