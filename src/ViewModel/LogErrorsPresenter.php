<?php

namespace App\ViewModel;

use App\ViewModel\LogErrorsViewModel;
use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorsPresenter extends AbstractPresenter
{
    public function present(Paginator $logErrors): void
    {
        if (!empty($logErrors)) {
            $this->viewModel = LogErrorsViewModel::fromLogErrors($logErrors, $this->data);
        } else {
            $this->viewModel = new LogErrorsViewModel();
        }
    }


    public function viewModel(): LogErrorsViewModel
    {
        return $this->viewModel;
    }

}