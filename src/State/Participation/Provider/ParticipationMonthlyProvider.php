<?php

declare(strict_types=1);

namespace App\State\Participation\Provider;

use App\Dto\View\LineShart\LineShartItemView;
use App\Dto\View\LineShart\LineShartView;
use App\Repository\SessionRepository;
use DateInterval;
use DateTimeImmutable;

class ParticipationMonthlyProvider
{
    public function __construct(
        private SessionRepository $sessionRepository,
    ) {
    }

    public function mapToView(bool $isSchool): LineShartView
    {
        $today = new DateTimeImmutable();
        
        return new LineShartView([
            new LineShartItemView(
                data: $this->sessionRepository->findParticipation(
                    $isSchool,
                    $today->sub(new DateInterval('P6M')),
                    $today
                ),
                lineColor: "rgba(230,132,27,1)"
            )
        ]);
    }
}
