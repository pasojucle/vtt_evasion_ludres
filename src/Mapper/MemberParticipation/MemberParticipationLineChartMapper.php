<?php

declare(strict_types=1);

namespace App\Mapper\MemberParticipation;

use App\Dto\View\LineChart\LineChartItemView;
use App\Dto\View\LineChart\LineChartPointView;
use App\Dto\View\LineChart\LineChartView;
use DateTimeImmutable;
use IntlDateFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;

class MemberParticipationLineChartMapper
{
    private IntlDateFormatter $dateFormatter;
    public function __construct(
    ) {
        $this->dateFormatter = new IntlDateFormatter(
            'fr_FR',
            IntlDateFormatter::NONE,
            IntlDateFormatter::NONE,
            null,
            null,
            'MMM yy'
        );
    }

    /**
     * @param DateTimeImmutable[] $period
     */
    public function mapToView(
        array $participations,
        array $period,
    ): string {
        $chartView = new LineChartView([
            new LineChartItemView(
                points: array_map(fn ($date) =>
                    new LineChartPointView(
                        label: ucfirst($this->dateFormatter->format($date)),
                        total: $participations[$date->format('Y-m')] ?? 0,
                    ), $period),
                lineColor: "rgba(230,132,27,1)"
            )
        ]);

        return json_encode($chartView->items);
    }
}
