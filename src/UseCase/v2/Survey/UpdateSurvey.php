<?php

declare(strict_types=1);

namespace App\UseCase\v2\Survey;

use App\Entity\Survey;
use App\Repository\Interface\SurveyRepositoryInterface;

class UpdateSurvey
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
    ) {
    }

    public function __invoke(Survey $survey): void
    {
        $this->surveyRepository->save($survey);
    }
}
