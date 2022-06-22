<?php

declare(strict_types=1);

namespace App\ViewModel\Background;

use App\Entity\Background;
use App\ViewModel\AbstractPresenter;

class BackgroundPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Background $background): void
    {
        if (null !== $background) {
            $this->viewModel = BackgroundViewModel::fromBackground($background, $this->services);
        } else {
            $this->viewModel = new BackgroundViewModel();
        }
    }

    public function viewModel(): BackgroundViewModel
    {
        return $this->viewModel;
    }
}
