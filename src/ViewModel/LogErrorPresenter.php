<?php

namespace App\ViewModel;

use App\Entity\LogError;
use App\ViewModel\OrderViewModel;

class LogErrorPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?LogError $logError): void
    {
        if (null !== $logError) {
            $this->viewModel = LogErrorViewModel::fromLogError($logError, $this->services);
        } else {
            $this->viewModel = new OrderViewModel();
        }
    }


    public function viewModel(): LogErrorViewModel
    {
        return $this->viewModel;
    }

}