<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class ListDrawerItemView
{
    /**
     * @param string $label
     * @param BadgeView[] $indicators
     * @param BadgeView $status
     * @param string $url
     */
    public function __construct(
        public string $label,
        public array $indicators,
        public BadgeView $status,
        public string $url,
    ) {
    }
}
