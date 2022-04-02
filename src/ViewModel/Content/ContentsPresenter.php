<?php

declare(strict_types=1);

namespace App\ViewModel\Content;

use App\ViewModel\AbstractPresenter;

class ContentsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array $contents): void
    {
        if (!empty($contents)) {
            $this->viewModel = ContentsViewModel::fromContents($contents, $this->services);
        } else {
            $this->viewModel = new ContentsViewModel();
        }
    }

    public function viewModel(): ContentsViewModel
    {
        return $this->viewModel;
    }
}
