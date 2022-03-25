<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\SurveyResponse;

class SurveyResponsePresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?SurveyResponse $surveyResponse): void
    {
        if (null !== $surveyResponse) {
            $this->viewModel = SurveyResponseViewModel::fromSurveyResponse($surveyResponse, $this->services);
        } else {
            $this->viewModel = new SurveyResponseViewModel();
        }
    }

    public function viewModel(): SurveyResponseViewModel
    {
        return $this->viewModel;
    }
}
