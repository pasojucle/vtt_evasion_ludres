<?php

declare(strict_types=1);

namespace App\Dto\View\LineChart;

readonly class LineChartItemView
{
    /**
    * @param LineChartPointView[] $points
    * @param string $lineColor
    */
    public function __construct(
        public array $points,
        public string $lineColor,
    ) {
    }
}
