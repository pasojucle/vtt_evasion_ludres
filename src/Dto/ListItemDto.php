<?php

declare(strict_types=1);

namespace App\Dto;

readonly class ListItemDto
{
    /**
     * @param LabelDto[] $labels
     * @param ?BadgeDto[] $indicators
     * @param ?BadgeDto $status
     * @param ?BadgeDto $counter
     * @param ?DropdownDto $dropdown
     * @param ?string  $background
     * @param ?string $url
     * @param ?ButtonDto $action
     */
    public function __construct(
        public array $labels = [],
        public ?array $indicators = null,
        public ?BadgeDto $status = null,
        public ?BadgeDto $counter = null,
        public ?DropdownDto $dropdown = null,
        public ?string $background = null,
        public ?string $url = null,
        public ?ButtonDto $action = null,
    ) {
    }
}
