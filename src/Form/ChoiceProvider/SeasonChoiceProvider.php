<?php

declare(strict_types=1);

namespace App\Form\ChoiceProvider;

use App\Service\SeasonService;

class SeasonChoiceProvider
{
    public function __construct(
        private SeasonService $seasonService,
    ) {
    }

    public function getChoices(): array
    {
        $choices = [];
        foreach (range(2021, $this->seasonService->getCurrentSeason()) as $season) {
            $choices['Saison ' . $season] = $season;
        }

        return array_reverse($choices);
    }
}