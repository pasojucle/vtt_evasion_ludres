<?php

declare(strict_types=1);

namespace App\ViewModel;

use Doctrine\ORM\Tools\Pagination\Paginator;

class SessionsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Paginator $sessions): void
    {
        if (!empty($sessions)) {
            $this->viewModel = SessionsViewModel::fromSessions($sessions, $this->services);
        } else {
            $this->viewModel = new SessionsViewModel();
        }
    }

    public function viewModel(): SessionsViewModel
    {
        return $this->viewModel;
    }
}
