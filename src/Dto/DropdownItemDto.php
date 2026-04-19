<?php

declare(strict_types=1);

namespace App\Dto;

readonly class DropdownItemDto
{
    public function __construct(
        public string $label,
        public string $icon,
        public array $data = [],
    ) {
    }
}
