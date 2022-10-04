<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Entity\SurveyResponse;
use App\Repository\SurveyResponseRepository;

class GetAnonymousSurveyResults
{
    public function __construct(
        private SurveyResponseRepository $surveyResponseRepository
    ) {
    }

    public function execute(Survey $survey): array
    {
        $surveyResponsesByIssues = $this->surveyResponseRepository->findResponsesByIssues($survey);

        $results = [];
        $values = [];
        foreach (array_keys(SurveyResponse::VALUES) as $choice) {
            $values[$choice] = 0;
        }
        foreach ($surveyResponsesByIssues as $responses) {
            foreach ($responses as $response) {
                if (SurveyIssue::RESPONSE_TYPE_CHOICE === $response->getSurveyIssue()->getResponseType()) {
                    $surveyIssueId = $response->getSurveyIssue()->getId();
                    if (!array_key_exists($surveyIssueId, $results)) {
                        $results[$surveyIssueId]['results'] = $values;
                        $results[$surveyIssueId]['content'] = $response->getSurveyIssue()->getContent();
                    }
                    ++$results[$surveyIssueId]['results'][$response->getValue()];
                }
            }
        }

        return $results;
    }
}
