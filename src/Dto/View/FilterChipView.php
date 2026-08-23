<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class FilterChipView
{
    /**
     * Summary of __construct
     * @param string $label
     * @param string $url
     */
    public function __construct(
        public string $label,
        public ?string $url = null,
        public ?string $turboFrame = null,
    ) {
    }
}
