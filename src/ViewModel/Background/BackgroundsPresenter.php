<?php

declare(strict_types=1);

namespace App\ViewModel\Background;

use App\ViewModel\AbstractPresenter;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BackgroundsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Paginator $backgrounds): void
    {
        if (!empty($backgrounds)) {
            $this->viewModel = BackgroundsViewModel::fromBackgrounds($backgrounds, $this->services);
        } else {
            $this->viewModel = new BackgroundsViewModel();
        }
    }

    public function viewModel(): BackgroundsViewModel
    {
        return $this->viewModel;
    }
}
