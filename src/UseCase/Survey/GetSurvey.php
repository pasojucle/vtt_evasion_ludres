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
        $survey->setDisplayCriteria($this->getDisplayCriteria($survey));
    }

    private function getSurvey(?Survey &$survey): void
    {
        if (!$survey) {
            $survey = new Survey();
            $issue = new SurveyIssue();
            $survey->addSurveyIssue($issue);
        }
    }

    private function getDisplayCriteria(Survey $survey): ?int
    {
        switch (true) {
            case null !== $survey->getBikeRide():
                $displayCriteria = SurveyType::DISPLAY_BIKE_RIDE;
                break;
            case !$survey->getMembers()->isEmpty():

                $displayCriteria = SurveyType::DISPLAY_MEMBER_LIST;
                break;
            default:
            $displayCriteria = SurveyType::DISPLAY_ALL_MEMBERS;
        }

        return $displayCriteria;
    }
}
