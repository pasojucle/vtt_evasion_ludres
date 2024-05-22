<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Survey;
use App\Repository\HistoryRepository;

class SurveyService
{
    public function __construct(
        private readonly HistoryRepository $historyRepository
    ) {
    }

    public function getHistory(Survey $survey): array
    {
        $histories = $this->historyRepository->findBySurvey($survey);

        return $histories;
    }
}
