<?php

namespace App\ViewModel;

use App\Entity\User;
use App\ViewModel\UserViewModel;

class UserPresenter extends AbstractPresenter
{
    public function present(?User $user): void
    {
        if (null !== $user) {
            $this->viewModel = UserViewModel::fromUser($user, $this->data);
        } else {
            $this->viewModel = new UserViewModel();
        }
    }

    public function viewModel(): UserViewModel
    {
        return $this->viewModel;
    }

}