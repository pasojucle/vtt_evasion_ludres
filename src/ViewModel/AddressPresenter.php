<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\Address;

class AddressPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?Address $address): void
    {
        if (null !== $address) {
            $this->viewModel = AddressViewModel::fromAddress($address, $this->services);
        } else {
            $this->viewModel = new AddressViewModel();
        }
    }

    public function viewModel(): AddressViewModel
    {
        return $this->viewModel;
    }
}
