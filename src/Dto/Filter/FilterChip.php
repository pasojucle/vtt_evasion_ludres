<?php

declare(strict_types=1);

namespace App\Dto\Filter;

readonly class FilterChip
{
    /**
     * Summary of __construct
     * @param string $name
     * @param string $label
     */
    public function __construct(
        public string $name,
        public string $label,
    ) {
    }
}
