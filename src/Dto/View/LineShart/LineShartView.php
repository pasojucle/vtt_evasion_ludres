<?php

declare(strict_types=1);

namespace App\Dto\View\LineShart;

readonly class LineShartView
{
    /**
    * @param LineShartItemView[] $items
    */
    public function __construct(
        public array $items,
    ) {
    }
}
