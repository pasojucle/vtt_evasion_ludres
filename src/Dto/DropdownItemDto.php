<?php

declare(strict_types=1);

namespace App\Dto;

readonly class DropdownItemDto
{
    /**
     * @param string $label
     * @param string $icon
     * @param HtmlAttributDto[] $htmlAttributes
     */
    public function __construct(
        public readonly string $label,
        public readonly string $icon,
        public array $htmlAttributes = [],
    ) {

    }
}
