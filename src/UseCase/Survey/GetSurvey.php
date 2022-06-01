<?php

declare(strict_types=1);

namespace App\UseCase\Survey;

use App\Entity\Survey;
use App\Entity\SurveyIssue;
use App\Form\Admin\SurveyType;
use App\Service\ParameterService;

class GetSurvey
{
    public function __construct(
        private ParameterService $parameterService
    ) {
    }

    public function execute(?Survey &$survey): void
    {
        $this->getSurvey($survey);
        $survey->setDisplay($this->getDisplay($survey));

    }

    private function getSurvey(Survey &$survey): void
    {
        if (!$survey) {
            $survey = new Survey();
            $issue = new SurveyIssue();
            $survey->addSurveyIssue($issue);
        }

    }

    private function getDisplay(Survey $survey): ?int
    {
        switch(true) {
            case null !== $survey->getBikeRide():
                $display = SurveyType::DISPLAY_BIKE_RIDE;
                break;
            case !$survey->getMembers()->isEmpty():

                $display = SurveyType::DISPLAY_MEMBER_LIST;
                break;
            default:
            $display = SurveyType::DISPLAY_ALL_MEMBERS;
        }

        return $display;
    }
}
