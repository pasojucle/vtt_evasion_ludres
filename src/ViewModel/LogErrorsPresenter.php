<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorsPresenter extends AbstractPresenter
{
    public function present(Paginator $logErrors): void
    {
        if (!empty($logErrors)) {
            $this->viewModel = LogErrorsViewModel::fromLogErrors($logErrors, $this->services);
        } else {
            $this->viewModel = new LogErrorsViewModel();
        }
    }

    public function viewModel(): LogErrorsViewModel
    {
        return $this->viewModel;
    }
}
