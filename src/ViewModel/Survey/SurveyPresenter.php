<?php

declare(strict_types=1);

namespace App\ViewModel\Survey;

use App\Entity\Survey;
use App\ViewModel\AbstractPresenter;

class SurveyPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Survey $survey): void
    {
        if (null !== $survey) {
            $this->viewModel = SurveyViewModel::fromSurvey($survey, $this->services);
        } else {
            $this->viewModel = new SurveyViewModel();
        }
    }

    public function viewModel(): SurveyViewModel
    {
        return $this->viewModel;
    }
}
