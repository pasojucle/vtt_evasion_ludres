<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\LogError;

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
