<?php

declare(strict_types=1);

namespace App\ViewModel\Documentation;

use App\ViewModel\AbstractPresenter;
use Doctrine\Common\Collections\Collection;

class DocumentationsPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array|Collection $documentations): void
    {
        if (!empty($documentations)) {
            $this->viewModel = DocumentationsViewModel::fromDocumentations($documentations, $this->services);
        } else {
            $this->viewModel = new DocumentationsViewModel();
        }
    }

    public function viewModel(): DocumentationsViewModel
    {
        return $this->viewModel;
    }
}
