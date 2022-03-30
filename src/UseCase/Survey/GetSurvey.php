<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Service\ParameterService;

class GetSurvey
{
    public function __construct(
        private ParameterService $parameterService
    ) {
    }

    public function execute(?Survey &$survey)
    {
        if (!$survey) {
            $survey = new Survey();
            $issue = new SurveyIssue();
            $survey->addSurveyIssue($issue);
        }
    }
}
