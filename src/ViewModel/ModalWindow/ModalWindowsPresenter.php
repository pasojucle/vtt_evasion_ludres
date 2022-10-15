<?php

declare(strict_types=1);

namespace App\ViewModel\ModalWindow;

use App\ViewModel\AbstractPresenter;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ModalWindowsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Paginator $modalWindows): void
    {
        if (!empty($modalWindows)) {
            $this->viewModel = ModalWindowsViewModel::fromModalWindows($modalWindows, $this->services);
        } else {
            $this->viewModel = new ModalWindowsViewModel();
        }
    }

    public function viewModel(): ModalWindowsViewModel
    {
        return $this->viewModel;
    }
}
