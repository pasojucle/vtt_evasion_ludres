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
            $surveyIssues = $this->parameterService->getParameterByName('VOTE_ISSUES');
            $survey->setContent($this->parameterService->getParameterByName('VOTE_CONTENT'));
            if (!empty($surveyIssues)) {
                foreach ($surveyIssues as $surveyIssue) {
                    $issue = new SurveyIssue();
                    $issue->setContent($surveyIssue);
                    $survey->addSurveyIssue($issue);
                }
            }
        }
    }
}
