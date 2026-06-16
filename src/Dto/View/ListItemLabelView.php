<?php

declare(strict_types=1);

namespace App\Dto\View;

readonly class ListItemLabelView
{
    /**
     * @param LabelView[] $labels
     */
    public function __construct(
        public array $labels = [],
    ) {
    }
}
