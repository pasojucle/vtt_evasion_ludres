<?php

declare(strict_types=1);

namespace App\Dto\View\LineChart;

readonly class LineChartView
{
    /**
    * @param LineChartItemView[] $items
    */
    public function __construct(
        public array $items,
    ) {
    }
}
