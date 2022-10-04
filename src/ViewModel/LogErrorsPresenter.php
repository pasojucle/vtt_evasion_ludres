<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class LogErrorsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(Paginator $logErrors): void
    {
        if (0 !== $logErrors->count()) {
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
