<?php

declare(strict_types=1);

namespace App\ViewModel\Documentation;

use App\Entity\Documentation;
use App\ViewModel\AbstractPresenter;

class DocumentationPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Documentation $documentation): void
    {
        if (null !== $documentation) {
            $this->viewModel = DocumentationViewModel::fromDocumentation($documentation, $this->services);
        } else {
            $this->viewModel = new DocumentationViewModel();
        }
    }

    public function viewModel(): DocumentationViewModel
    {
        return $this->viewModel;
    }
}
