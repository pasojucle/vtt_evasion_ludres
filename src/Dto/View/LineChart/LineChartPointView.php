<?php

declare(strict_types=1);

namespace App\Dto\View\LineChart;

readonly class LineChartPointView
{
    public function __construct(
        public string $label,
        public int $total,
    ) {
    }
}
