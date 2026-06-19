<?php

declare(strict_types=1);

namespace App\Dto\View\LineShart;

readonly class LineShartItemView
{
    /**
    * @param array<int, array{count: int, startAt: \DateTimeInterface}> $data
    * @param string $lineColor
    */
    public function __construct(
        public array $data,
        public string $lineColor,
    ) {
    }
}
