<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Form\Admin\SurveyType;

class GetSurvey
{
    public function execute(?Survey &$survey): void
    {
        $this->getSurvey($survey);
        $survey->setRestriction($this->getRestriction($survey));
    }

    private function getSurvey(?Survey &$survey): void
    {
        if (!$survey) {
            $survey = new Survey();
            $issue = new SurveyIssue();
            $survey->addSurveyIssue($issue);
        }
    }

    private function getRestriction(Survey $survey): ?int
    {
        return match (true) {
            null !== $survey->getBikeRide() => SurveyType::DISPLAY_BIKE_RIDE,
            !$survey->getMembers()->isEmpty() => SurveyType::DISPLAY_MEMBER_LIST,
            default => SurveyType::DISPLAY_ALL_MEMBERS
        };
    }
}
