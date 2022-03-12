<?php

declare(strict_types=1);

namespace App\ViewModel;

use App\Entity\User;

class UserPresenter extends AbstractPresenter
{
    private $viewModel;

    public function present(?User $user): void
    {
        if (null !== $user) {
            $this->viewModel = UserViewModel::fromUser($user, $this->services);
        } else {
            $this->viewModel = new UserViewModel();
        }
    }

    public function viewModel(): UserViewModel
    {
        return $this->viewModel;
    }
}
