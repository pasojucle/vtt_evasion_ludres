<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class ListItemView
{
    /**
     * @param LabelView[] $labels
     * @param ?BadgeView[] $indicators
     * @param ?BadgeView $status
     * @param ?BadgeView $counter
     * @param ?DropdownView $dropdown
     * @param ?string  $background
     * @param ?string $url
     * @param ?ButtonView $action
     */
    public function __construct(
        public array $labels = [],
        public ?array $indicators = null,
        public ?BadgeView $status = null,
        public ?BadgeView $counter = null,
        public ?DropdownView $dropdown = null,
        public ?string $background = null,
        public ?string $url = null,
        public false | ButtonView | null $action = false,
        public string $gridTemplateContent = 'grid-cols-1 lg:grid-cols-2',
        public string $gridTemplateLabels = 'grid-cols-1',
        public string $gridTemplateBadges = 'grid-cols-1',
    ) {
    }
}
