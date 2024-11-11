<?php

declare(strict_types=1);

namespace App\Dto\DtoTransformer;

use App\Dto\SummaryDto;
use App\Entity\Summary;
use App\Service\BikeRideService;

class SummaryDtoTransformer
{
    public function __construct(
        private readonly BikeRideService $bikeRideService
    ) {
    }

    public function fromEntity(?Summary $summary, bool $novelty = false): SummaryDto
    {
        $summaryDto = new SummaryDto();
        if ($summary) {
            $summaryDto->createdAt = $summary->getCreatedAt()->format('d/m/Y');
            $summaryDto->title = $summary->getTitle();
            $summaryDto->content = $summary->getContent();
            $summaryDto->novelty = $novelty;
        }

        return $summaryDto;
    }

    public function fromEntities(array $summaries, array $summaryViewedIds = []): array
    {
        $summariesByBikeRide = [];
        /** @var Summary $summary */
        foreach ($summaries as $summary) {
            $bikeRide = $summary->getBikeRide();
            if (!array_key_exists($bikeRide->getId(), $summariesByBikeRide)) {
                $summariesByBikeRide[$bikeRide->getId()] = [
                    'title' => $bikeRide->getTitle(),
                    'period' => $this->bikeRideService->getPeriod($bikeRide),
                    'startAt' => $bikeRide->getStartAt(),
                ];
            }
            $summariesByBikeRide[$bikeRide->getId()]['summaries'][] = $this->fromEntity($summary, !in_array($summary->getId(), $summaryViewedIds));
        }

        $this->sortBikeRidesDesc($summariesByBikeRide);
        return $summariesByBikeRide;
    }

    private function sortBikeRidesDesc(array &$bikeRides): void
    {
        usort($bikeRides, function ($a, $b) {
            return $a['startAt'] < $b['startAt'] ? 1 : -1;
        });
    }
}
