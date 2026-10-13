<?php

declare(strict_types=1);

namespace App\Mapper\SurveyIssue;

use App\Entity\SurveyIssue;

class SurveyIssueAutocompleteMapper
{
    public function mapToChoices(array $surveyIssues): array
    {
        $results = [];
        /** @var SurveyIssue $surveyIssue */
        foreach ($surveyIssues as $surveyIssue) {
            $results[] = [
                'value' => $surveyIssue->getId(),
                'text' => $surveyIssue->getContent(),
            ];
        }

        return $results;
    }
}
