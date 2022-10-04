<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Repository\SurveyResponseRepository;

class GetSurveyResults
{
    public function __construct(
        private SurveyResponseRepository $surveyResponseRepository
    ) {
    }

    public function execute(array $filter): array
    {
        return $this->surveyResponseRepository->findByfilter($filter);
    }
}
