<?php

declare(strict_types=1);

namespace App\ViewModel\Session;

use App\Entity\Session;
use App\ViewModel\AbstractPresenter;

class SessionPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Session $session): void
    {
        if (null !== $session) {
            $this->viewModel = SessionViewModel::fromSession($session, $this->services);
        } else {
            $this->viewModel = new SessionViewModel();
        }
    }

    public function viewModel(): SessionViewModel
    {
        return $this->viewModel;
    }
}
