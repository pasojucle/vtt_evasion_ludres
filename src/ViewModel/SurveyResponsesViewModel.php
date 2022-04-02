<?php

declare(strict_types=1);

namespace App\ViewModel;

class SurveyResponsesViewModel
{
    public ?array $surveyResponses = [];

    public static function fromSurveyResponses(array $surveyResponses, ServicesPresenter $services): SurveyResponsesViewModel
    {
        $surveyResponsesViewModel = [];
        if (!empty($surveyResponses)) {
            foreach ($surveyResponses as $surveyResponse) {
                $surveyResponsesViewModel[] = SurveyResponseViewModel::fromSurveyResponse($surveyResponse, $services);
            }
        }

        $surveyResponsesView = new self();
        $surveyResponsesView->surveyResponses = $surveyResponsesViewModel;

        return $surveyResponsesView;
    }
}
