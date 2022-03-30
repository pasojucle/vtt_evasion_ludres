<?php

declare(strict_types=1);

namespace App\ViewModel\Content;

use App\Entity\Content;
use App\ViewModel\AbstractPresenter;
use App\ViewModel\Content\ContentViewModel;

class ContentPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Content $content): void
    {
        if (null !== $content) {
            $this->viewModel = ContentViewModel::fromContent($content, $this->services);
        } else {
            $this->viewModel = new ContentViewModel();
        }
    }

    public function viewModel(): ContentViewModel
    {
        return $this->viewModel;
    }
}
