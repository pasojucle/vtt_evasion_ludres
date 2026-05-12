<?php

declare(strict_types=1);

namespace App\Dto;

readonly class SheetDto
{
    public function __construct(
        public string $title,
        public string $description,
        public string $action,
    ) {
    }
}
