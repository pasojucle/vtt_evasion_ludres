<?php

declare(strict_types=1);

namespace App\ViewModel\Indemnity;

use App\ViewModel\AbstractPresenter;

class IndemnitiesPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(array $indemnities): void
    {
        if (!empty($indemnities)) {
            $this->viewModel = IndemnitiesViewModel::fromIndemnities($indemnities);
        } else {
            $this->viewModel = new IndemnitiesViewModel();
        }
    }

    public function viewModel(): IndemnitiesViewModel
    {
        return $this->viewModel;
    }
}
