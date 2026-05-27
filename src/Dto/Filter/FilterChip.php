<?php

declare(strict_types=1);

namespace App\Dto\Filter;

readonly class FilterChip
{
    /**
     * Summary of __construct
     * @param string $label
     * @param string $url
     */
    public function __construct(
        public string $label,
        public string $url,
    ) {
    }
}
