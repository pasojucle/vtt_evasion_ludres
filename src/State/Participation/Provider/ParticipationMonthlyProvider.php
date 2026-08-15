<?php

declare(strict_types=1);

namespace App\State\Participation\Provider;

use App\Dto\View\LineChart\LineChartItemView;
use App\Dto\View\LineChart\LineChartPointView;
use App\Dto\View\LineChart\LineChartView;
use App\Repository\SessionRepository;
use DateInterval;
use DateTimeImmutable;

class ParticipationMonthlyProvider
{
    public function __construct(
        private SessionRepository $sessionRepository,
    ) {
    }

    public function mapToView(bool $isSchool): LineChartView
    {
        $today = new DateTimeImmutable();
        $participations = $this->sessionRepository->findParticipation(
            $isSchool,
            $today->sub(new DateInterval('P12M')),
            $today
        );

        return new LineChartView([
            new LineChartItemView(
                points: array_map(fn ($participation) =>
                    new LineChartPointView(
                        total: $participation['total'],
                        label: $participation['date']->format('d/m/y')
                    ), $participations),
                lineColor: "rgba(230,132,27,1)"
            )
        ]);
    }
}
