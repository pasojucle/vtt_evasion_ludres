<?php

declare(strict_types=1);

namespace App\UseCase\v2\Survey;

use App\Entity\Survey;
use App\Repository\Interface\SurveyRepositoryInterface;
use App\Service\DisableService;

class ToggleSurvey
{
    public function __construct(
        private SurveyRepositoryInterface $surveyRepository,
        private DisableService $disableService,
    ) {
    }

    public function __invoke(Survey $survey): void
    {
        $this->disableService->toggle($survey);
        
        $this->surveyRepository->save($survey);
    }
}
