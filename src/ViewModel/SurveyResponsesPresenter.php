<?php

declare(strict_types=1);

namespace App\ViewModel;

class SurveyResponsesPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array $surveyResponses): void
    {
        if (!empty($surveyResponses)) {
            $this->viewModel = SurveyResponsesViewModel::fromSurveyResponses($surveyResponses, $this->services);
        } else {
            $this->viewModel = new SurveyResponsesViewModel();
        }
    }

    public function viewModel(): SurveyResponsesViewModel
    {
        return $this->viewModel;
    }
}
